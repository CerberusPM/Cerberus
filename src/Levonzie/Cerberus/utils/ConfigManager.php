<?php

/**
 * Cerberus - an advanced land protection plugin for PocketMine-MP 5.
 * Copyright (C) 2023 Levonzie
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Levonzie\Cerberus\utils;

use pocketmine\utils\TextFormat;

use Levonzie\Cerberus\Cerberus;
use Levonzie\Cerberus\utils\LangManager;

use function is_file;
use function yaml_parse_file;
use function version_compare;
use function rename;

/**
 * A class for plugin configuration management
 */
class ConfigManager {
    private static ConfigManager $instance;
    
    private Cerberus $plugin;
    
    private array $settings;
    
    private function __construct() {
        $this->plugin = Cerberus::getInstance();
        
        $this->loadConfig();
    }
    
    /**
     * Get ConfigManager instance
     * 
     * @return ConfigManager ConfigManager instance
     */
    public static function getInstance(): ConfigManager {
        if (!isset(self::$instance)) {
            self::$instance = new ConfigManager();
        }
        
        return self::$instance;
    }
    
    /**
     * Get a value of a setting from config.yml by setting name
     * 
     * @param string $setting     Setting name from config.yml
     * @param bool   $ignore_null Do not throw an exception when option is not found in the config
     * 
     * @return mixed Returns a value of corresponding setting in config.yml. Throws an exception or returns null (when $ignore_null is set to false) if requested setting is not found
     */
    public function get(string $setting, bool $ignore_null=false) {
        try {
            return $this->settings[$setting];
        } catch (\ErrorException) {
            if ($ignore_null)
                return null;
            else
                Throw new \RuntimeException("Option $setting does not exist in the config");
        }
    }
    
    /**
     * Get plugin prefix set in config.yml or, if not set, the default prefix
     * 
     * @return string Colorized prefix from config or default one
     */
    public function getPrefix(): string {
        try {
            return TextFormat::colorize($this->settings["prefix"]);
        } catch (\ErrorException) { //Prefix is not set in the config
            return "§l§2+§e-§6Cerberus§e-§2+§r ";
        }
    }
    
    /**
     * Reload the configuration
     */
    public function reload(): void {
        $this->plugin->getConfig()->reload();
        $this->loadConfig();
    }
    
    private function loadConfig(): void {
        $existing_conf_path = $this->plugin->getDataFolder() . "config.yml";
        $conf_already_existed = is_file($existing_conf_path);
        $conf_updated = false;
        
        $config = $this->plugin->getConfig();

        if ($conf_already_existed) {
            $existing_conf_version = $config->get("version");
            $embedded_conf_path = $this->plugin->getResourcePath("config.yml");
            $embedded_conf_version = yaml_parse_file($embedded_conf_path)["version"];
            
            if (version_compare($existing_conf_version, $embedded_conf_version) < 0) { //Embedded config is newer. Fires even when verison is not set and config file is an empty array
                @rename($existing_conf_path, $existing_conf_path . ".old"); //Backup the old config
                $this->plugin->saveDefaultConfig(); //Create new config
                $conf_updated = true;
            }
            $config->reload();
        }
        
        $this->settings = $config->getAll();
        
        if ($conf_updated) //We can use LangManager only after settings are loaded
            $this->plugin->getLogger()->warning(LangManager::getInstance()->translate("plugin.outdated_config", ["$existing_conf_path.old"])); //Notify user
        //TODO: Make the config retain settings after update
    }
}
