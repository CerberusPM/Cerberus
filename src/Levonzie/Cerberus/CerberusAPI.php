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

declare(strict_types=1);

namespace Levonzie\Cerberus;

use Levonzie\Cerberus\Cerberus;

/**
 * An API class which provides all necessary land management methods used by subcommands
 */

class CerberusAPI {
    //TODO all api stuff here
    
    private static CerberusAPI $instance;
    private Cerberus $plugin;
    
    private $version = "1.0.0-DEV";
    
    private function __construct() {
        $this->plugin = Cerberus::getInstance();
    }
    
    /*
     * Get API class instance
     * 
     * @return CerberusAPI CerberusAPI instance
     */
    public static function getInstance(): CerberusAPI {
        if (!isset(self::$instance)) {
            self::$instance = new CerberusAPI();
        }
        
        return self::$instance;
    }
    
    /**
     * Get API version
     * 
     * @return string version
     */
    public function getVersion(): string {
        return $this->version;
    }
    
    /**
     * Get owning plugin (Cerberus) instance
     * 
     * @return Cerberus Cerberus instance
     */
    public function getOwningPlugin(): Cerberus {
        return $this->plugin;
    }
}
