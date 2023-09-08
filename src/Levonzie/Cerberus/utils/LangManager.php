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

declare(strict_types=1);

namespace Levonzie\Cerberus\utils;

use pocketmine\utils\TextFormat;

use Levonzie\Cerberus\Cerberus;
use Levonzie\Cerberus\utils\ConfigManager;

use function mkdir;
use function is_file;
use function str_replace;
use function yaml_parse_file;
use function is_array;
use function version_compare;
use function rename;
/**
 * A class which provides capabilities for plugin messages translation by handling language files and making sure they are up to date.
 */

class LangManager {
    private static LangManager $instance;
    private Cerberus $plugin;
    
    private string $current_language;
    private array $translations;
    
    private function __construct() {
        //Load selected language.
        $this->plugin = Cerberus::getInstance();
        $this->loadLanguages();
    }
    
    private function loadLanguages(): void {
        $selected_language = ConfigManager::getInstance()->get("language", true);
        if (!isset($selected_language)) {
            $this->plugin->getLogger()->notice("Language option is not set in config.yml. English will be used by default.");
            $selected_language = "eng";
        }
        $selected_language = str_replace(".yml", "", $selected_language); //In case somebody will add .yml at the end
        
        @mkdir($this->plugin->getDataFolder() . "languages");
        
        $selected_lang_path = $this->plugin->getDataFolder() . "languages/$selected_language.yml";
        if (!is_file($selected_lang_path)) { //Create language file and load
            $saved_file = $this->plugin->saveResource("languages/$selected_language.yml");
            if (!$saved_file) { //Language file was not created
                Throw new \RuntimeException("Specified language $selected_language is not available. Please make sure you use one of the available languages (eng, rus), or manually added appropriate language file in plugin's languages folder.");
            } else {
                $language_contents = yaml_parse_file($selected_lang_path);
                $this->translations = $language_contents;
            }
        } else { //Language version check and load
            $language_contents = yaml_parse_file($selected_lang_path);
            if (!is_array($language_contents)) {
                Throw new \RuntimeException("$selected_language language file is not a valid YAML file or is empty. Please check the syntax");
            } else { //version check
                $embedded_langfile_path = $this->plugin->getResourcePath("languages/$selected_language.yml");
                $embedded_langfile_contents = yaml_parse_file($embedded_langfile_path);
                
                if (version_compare($language_contents["language-version"], $embedded_langfile_contents["language-version"]) < 0) { //Language version of the language_file in plugin_data is lower. Language file has to be updated
                    @rename($selected_lang_path, $selected_lang_path . '.old'); //Backup the old language file
                    $this->plugin->saveResource("languages/$selected_language.yml", true);
                    $this->plugin->getLogger()->warning("$selected_language language file is outdated and has been updated. The old file was backed up as $selected_lang_path.old");
                }
                
                $this->translations = $language_contents;
            }
        }
        $this->current_language = $selected_language;
    }
    
    /**
     * Translate a message by key into the language set in config.yml
     * 
     * @param string   $key    Translation message key set in language files.
     * @param string[] $params Array of values that will replace index variables (e.g., {%0}, {%1}) with corresponding values.
     * 
     * @return string Returns colorized string of the translation corresponding to $key
     */
    public function translate(string $key, array $params = []): string {
        $translation = $this->translations[$key] ?? Throw new \RuntimeException("Translation $key was not found in $this->current_language language file!");
        
        foreach ($params as $index => $param) {
            $translation = str_replace("{%$index}", $param, $translation);
        }
        
        return TextFormat::colorize($translation);
    }
    
    /**
     * Get current language key as it's set in config.yml
     * 
     * @return string Language key set in the config
     */
    public function getCurrentLanguage(): string {
        return $this->current_language;
    }
    
    /**
     * Get LangManager instance
     * 
     * @return LangManager LangManager instance
     */
    public static function getInstance(): LangManager {
        if (!isset(self::$instance)) {
            self::$instance = new LangManager();
        }
        
        return self::$instance;
    }

}
