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

namespace CerberusPM\Cerberus\utils;

use CerberusPM\Cerberus\Cerberus;
use CerberusPM\Cerberus\utils\ConfigManager;
use CerberusPM\Cerberus\flags\Flag;
use CerberusPM\Cerberus\flags\NoBreakFlag;
use CerberusPM\Cerberus\flags\NoPlaceFlag;
use CerberusPM\Cerberus\flags\NoInteractFlag;

use function in_array;
use function strtolower;

class FlagManager {
    
    private static FlagManager $instance;
    private Cerberus $plugin;
    
    private array $flagMap;
    
    private array $default_flags;
    
    private function __construct() {
        $this->plugin = Cerberus::getInstance();
        $this->default_flags = [
            new NoBreakFlag(),
            new NoPlaceFlag(),
            new NoInteractFlag()
        ]; // We are going to reuse the same flag objects
        $this->registerDefaultFlags();
    }
    
    /**
     * Get FlagManager instance
     * 
     * @return FlagManager FlagManager instance
     */
    public static function getInstance(): FlagManager {
        if (!isset(self::$instance)) {
            self::$instance = new FlagManager();
        }
        
        return self::$instance;
    }
    
    /**
     * Get an array of all registered flags
     * 
     * @return array[Flag] An array of all registered flags
     */
    public function getFlags(): array {
        return $this->flagMap;
    }
    
    /**
     * Register a flag in the flag map
     * 
     * @param Flag $flag an instance of flag class to register
     */
    public function registerFlag(Flag $flag): void {
        if (!in_array($flag->getId(), array_keys(ConfigManager::getInstance()->get("enabled-flags")))) {
            return;
        } // Consider unspecified flags as turned off
        if (!ConfigManager::getInstance()->get("enabled-flags")[$flag->getId()]) {
            return; // Don't register disabled flags
        }
        // Add flag to the flag map
        $this->flagMap[$flag->getId()] = $flag;
        
        if (!$flag->isRegistered()) {
            // Register flag's events
            $this->plugin->getServer()->getPluginManager()->registerEvents($flag, $this->plugin);
            // Mark flag as registered
            $flag->setRegistered(true);
        }
    }
    
    public function reload(): void {
        // Set flags as unregistered to disable their events (there's no way to unregister events)
        foreach ($this->default_flags as $flag) {
            $flag->clearAffectedLandclaims();
            $flag->setRegistered(False);
            $this->registerFlag($flag);
        }
    }
    
    /**
     * Register all plugin's built-in flags
     */
    private function registerDefaultFlags(): void {
        foreach ($this->default_flags as $flag) {
            $this->registerFlag($flag);
        }
    }
    
    /**
     * Get a registered flag class instance by its ID
     * 
     * @param string $flag_id Flag's ID
     * @return Flag|null Flag instance if it's found, null if not
     */
    public function getFlagById(string $flag_id): Flag|null {
        if (in_array($flag_id, array_keys($this->flagMap))) {
            return $this->flagMap[$flag_id];
        }
        return null;
    }
    
    
    /**
     * Get flag by id, name or name alias. Automatically converts input
     * string to lowercase
     * 
     * @param string $name Name, ID, or name alias case-insensitive
     * 
     * @return Flag|null Flag if such flag found, null if not
     */
    public function getFlagByName(string $name): Flag|null {
        $name = strtolower($name);
        foreach ($this->flagMap as $flag) {
            if ((strtolower($flag->getName()) == $name)
                    || $flag->getId() == $name || in_array($name, $flag->getNameAliases())) {
                return $flag;
            }
        }
        return null;
    }
    
}
