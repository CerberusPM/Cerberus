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

namespace CerberusPM\Cerberus\flags;

use CerberusPM\Cerberus\Landclaim;
use CerberusPM\Cerberus\utils\LangManager;

use function array_push;
use function array_search;

abstract class Flag {
    
    protected string $name;
    /** Different possible names for the flag. Should be lowercase without spaces **/
    protected array $name_aliases = [];
    /** Translation string for flag description **/
    protected string $description;
    /** Unique flag identifier for internal usage **/
    protected string $id;
    protected string $permission = "";
    protected bool $is_registered = false;
    protected array $landclaims = [];
    
    /**
     * Get flag's name
     * 
     * @return string Flag's name
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * Get flag's description
     * 
     * @return string Flag's description
     */
    public function getDescription(): string {
        return LangManager::getInstance()->translate($this->description, include_prefix: false);
    }
    
    /**
     * Get flag's id
     * 
     * @return string Flag's id
     */
    public function getId(): string {
        return $this->id;
    }
    
    /**
     * Get flag's permission - a permission players should have
     * in order to be able to use this flag
     * 
     * @return string Flag's permission
     */
    public function getPermission(): string {
        return $this->permission;
    }
    
    /**
     * Returns an array of name aliases. They should be lowercase
     * 
     * @return array[string] Name aliases
     */
    public function getNameAliases(): array {
        return $this->name_aliases;
    }
    
    /**
     * Get affected landclaims, i.e. landclaims, which have this flag enabled
     * 
     * @return array An array of affected landclaims
     */
    public function getAffectedLandclaims(): array {
        return $this->landclaims;
    }
    
    /**
     * Enable this flag in the specified landclaim
     * 
     * @param Landclaim $land A landclaim to be added
     */
    public function addAffectedLandclaim(Landclaim $land): void {
        array_push($this->landclaims, $land);
    }
    
    /**
     * Disable this flag in the specified landclaim
     * 
     * @param Landclaim $land A landclaim to be removed
     */
    public function removeAffectedLandclaim(Landclaim $land): void {
        unset($this->landclaims[array_search($land, $this->landclaims)]);
    }
    
    /**
     * Check whether this flag is registered in FlagManager
     * 
     * @return bool Whether this flag is registered
     */
    public function isRegistered(): bool {
        return $this->is_registered;
    }
    
    /**
     * Mark this flag as registered
     * 
     * @param bool $value
     * @return void
     */
    public function setRegistered(bool $value): void {
        $this->is_registered = $value;
    }
    
    /**
     * Clear affected landclaim list
     * 
     * @return void
     */
    public function clearAffectedLandclaims(): void {
        $this->landclaims = array();
    }
}
