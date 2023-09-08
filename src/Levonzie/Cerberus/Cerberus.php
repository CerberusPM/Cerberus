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

namespace Levonzie\Cerberus;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;

use CortexPE\Commando\PacketHooker;

use Levonzie\Cerberus\command\CerberusCommand;
use Levonzie\Cerberus\CerberusAPI;
use Levonzie\Cerberus\utils\ConfigManager;

class Cerberus extends PluginBase {
    
    private static Cerberus $instance; //Unique instance. Singleton class
    
    private CerberusAPI $api;
    private ConfigManager $config_manager;
    
    public function onEnable(): void {
        $this->getServer()->getCommandMap()->register("Cerberus", new CerberusCommand($this, "cerberus", "protect land", ["crb"]));
        
        if(!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
        
        self::$instance = $this;
        
        $this->api = CerberusAPI::getInstance();
        $this->config_manager = ConfigManager::getInstance();
        
        $this->getLogger()->info("Cerberus API version: " . $this->api->getVersion()); //For testing purpose
        $this->getLogger()->info("Selected language: " . $this->config_manager->get("language"));
    }
    
    public static function getInstance(): Cerberus {
        return self::$instance;
    }
    
    public function getAPI(): CerberusAPI {
        return $this->api;
    }
    
    public function getConfigManager(): ConfigManager {
        return $this->config_manager;
    }
    
}
