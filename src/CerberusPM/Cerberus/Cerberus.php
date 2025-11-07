<?php

/**
 * Cerberus - an advanced land protection plugin for PocketMine-MP 5.
 * Copyright (C) 2025 CerberusPM
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

namespace CerberusPM\Cerberus;

use pocketmine\plugin\PluginBase;
use CortexPE\Commando\PacketHooker;

use CerberusPM\Cerberus\command\CerberusCommand;
use CerberusPM\Cerberus\utils\ConfigManager;
use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\utils\LandManager;
use CerberusPM\Cerberus\utils\FlagManager;

use CerberusPM\Cerberus\listeners\WandSelectionListener;
use CerberusPM\Cerberus\listeners\BlockBreakListener;

class Cerberus extends PluginBase {
    
    private static Cerberus $instance; //Unique instance. Singleton class
    
    private LangManager $lang_manager;
    private LandManager $land_manager;
    private ConfigManager $config_manager;
    private FlagManager $flag_manager;
    private CerberusCommand $base_command;
    
    public function onLoad(): void {
        self::$instance = $this;
    }
    
    public function onEnable(): void {
        $this->lang_manager = LangManager::getInstance();
        
        $this->base_command = new CerberusCommand($this, "cerberus", "protect land", ["crb", "cerb"]);
        $this->getServer()->getCommandMap()->register("Cerberus", $this->base_command);
        
        if(!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
        
        $this->config_manager = ConfigManager::getInstance();
        $this->land_manager = LandManager::getInstance();
        $this->flag_manager = FlagManager::getInstance();
        
        $this->getLogger()->notice($this->lang_manager->translate("plugin.in-dev", include_prefix: false));
        $this->getLogger()->info($this->lang_manager->translate("plugin.version", [$this->getDescription()->getVersion()], false));
        $this->getLogger()->info($this->lang_manager->translate("plugin.selected_language", include_prefix: false));
        
        $this->getServer()->getPluginManager()->registerEvents(new WandSelectionListener($this), $this);
    }
    
    /**
     * Get the main class instance
     * 
     * @return Cerberus Plugin's main class instance
     */
    public static function getInstance(): Cerberus {
        return self::$instance;
    }
    
    /**
     * Get ConfigManager instance
     * 
     * @return ConfigManager ConfigManager instance
     */
    public function getConfigManager(): ConfigManager {
        return $this->config_manager;
    }
    
    /**
     * Get LangManager instance
     * 
     * @return LangManager LangManager instance
     */
    public function getLangManager(): LangManager {
        return $this->lang_manager;
    }
    
    /**
     * Get LandManager instance
     * 
     * @return LandManager LandManager instance
     */
    public function getLandManager(): LandManager {
        return $this->land_manager;
    }
    
    /**
     * Get FlagManager instance
     * 
     * @return FlagManager FlagManager instance
     */
    public function getFlagManager(): FlagManager {
        return $this->flag_manager;
    }
    
    /**
     * Get base cerberus command class instance
     * 
     * @return CerberusCommand CerberusCommand instance
     */
    public function getCerberusCommand(): CerberusCommand {
        return $this->base_command;
    }
    
    /**
     * Returns the full path to a data file in the plugin's resources folder.
     * 
     * This method is available in PocketMine since API 5.5.0. It's added here for compatibility with older PocketMine versions.
     */
    public function getResourcePath(string $filename): string {
        return $this->getFile() . "/resources/" . $filename;
    }
}
