<?php

/**
 * Cerberus - an advanced land protection plugin for PocketMine-MP 5.
 * Copyright (C) 2023 skyss0fly and Levonzie
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

use function is_file;
/**
 * A class for plugin configuration management
 */

class ConfigManager {
    private static ConfigManager $instance;
    
    public const CONFIG_VERSION = "1.0-DEV";
    
    private Cerberus $plugin;
    
    private array $settings;
    
    private function __construct() {
        $this->plugin = Cerberus::getInstance();
        
        $this->loadConfig();
        
        
    }
    
    private function loadConfig(): void {
        if (is_file($this->plugin->getDataFolder() . "config.yml")) {
            //TODO: Check if config is okay and implement version check
        } else {
            $this->plugin->getInstance()->saveDefaultConfig();
        }
        $this->settings = $this->plugin->getConfig()->getAll();
    }
    
    public static function getInstance(): ConfigManager {
        if (!isset(self::$instance)) {
            self::$instance = new ConfigManager();
        }
        
        return self::$instance;
    }
    
    public function get($setting, bool $ignore_null=false) {
        if ($ignore_null) {
            return $this->settings[$setting];
        } else {
            return $this->settings[$setting] ?? Throw new \LogicException("Option $setting does not exist in the config");
        }
    }
    
    public function getPrefix(): string {
        try {
            return TextFormat::colorize($this->settings["prefix"]);
        } catch (\ErrorException) { //Prefix is not set in the config
            return "§l§2+§e-§6Cerberus§e-§2+§r ";
        }
    }
}
