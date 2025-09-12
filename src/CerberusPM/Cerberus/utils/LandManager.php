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

use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\math\Vector3;

use CerberusPM\Cerberus\Landclaim;
use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\exception\LandExistsException;
use CerberusPM\Cerberus\exception\LandIntersectException;

use function is_null;
use function array_push;
use function implode;
use function array_key_exists;

/**
 * Class for landclaim management
 */
class LandManager {
    private static LandManager $instance;
     
    private array $landclaims = [];
    
    private function __construct() { }
    
    /**
     * Get LandManager instance
     * 
     * @return LangManager LangManager instance
     */
    public static function getInstance(): LandManager {
        if (!isset(self::$instance)) {
            self::$instance = new LandManager();
        }
        
        return self::$instance;
    }
    
    /**
     * Register a landclaim
     * 
     * @param Landclaim $land A landclaim to register
     * 
     * @throws LandExistsException if a landclaim already exists   
     */
    public function registerLandclaim(Landclaim $land): void {
        $land_name = $land->getName();
        if ($this->exists($land_name)) {
            Throw new LandExistsException("Landclaim named $land_name already exists!");
        }
        $this->landclaims[$land_name] = $land;
    }
    
    /**
     * Unregister (delete) a landclaim
     * 
     * @param string $land_name Name of a landclaim to unregister
     */
    public function unregisterLandclaim(string $land_name): void {
        if ($this->exists($land_name)) {
            unset($this->landclaims[$land_name]);
        }
    }
    
    /**
     * @return Landclaim[] Array of all registered landclaims
     */
    public function getLandclaims(): array {
        return $this->landclaims;
    }
    
    /**
     * Check if landclaim with given name exists
     * 
     * @param string $land_name Land name to be checked for existance
     * 
     * @return bool Whether land exists or not
     */
    public function exists(string $land_name): bool {
        return array_key_exists($land_name, self::getLandclaims());
    }
    
    /**
     * Create a landclaim with all the necessary checks (primarily for external usage)
     * 
     * @param string  $land_name              Name of the landclaim (should be unique)
     * @param Player  $land_creator           Landclaim creator
     * @param Vector3 $pos1                   First position of the landclaim
     * @param Vector3 $pos2                   Second position of the landclaim
     * @param string  $world_name             Folder name of the world, where the landclaim will be created
     * @param bool    $check_for_intersection Whether to check if specified landclaim intersects a landclaim of another owner
     *
     * @throws LandExistsException    if a landclaim with given $land_name already exists
     * @throws LandIntersectException if intersection check is performed and resulting landclaim intersects a landclaim of another owner
     */
    public function createLand(string $land_name, Player $land_creator, Vector3 $pos1, Vector3 $pos2, string $world_name, bool $check_for_intersection=true): void {
        if ($this->exists($land_name)) {
            Throw new LandExistsException("Landclaim named $land_name already exists!");
        }
        $land = new Landclaim($land_name, $land_creator, $pos1, $pos2, $world_name);
        if ($check_for_intersection) {
            $intersecting_land = $this->getIntersectingLand($land);
            if (!is_null($intersecting_land) && !$intersecting_land->isOwner($land_creator)) {
                Throw new LandIntersectException("Resulting landclaim intersects landclaim" 
                        . $intersecting_land->getName() . " owned by " 
                        . implode(", ", $intersecting_land->getOwnerNames()), 0, null, $land, $intersecting_land);
            }
        }
        LandManager::registerLandclaim($land);
    }
    
    /**
     * Get landclaim with given name or null if it doesn't exist
     * 
     * @param string $land_name Name of a landclaim to get.
     * 
     * @return Landclaim|null Landclaim if exists, null if doesn't exist
     */
    public function getLandByName(string $land_name): Landclaim|null {
        if ($this->exists($land_name)) {
            return $this->landclaims[$land_name];
        } else {
            return null;
        }
    }
    
    /**
     * Get an array of landclaims containing position
     * 
     * @param Position $position                Position to be checked for inclusion in a landclaim
     * @param bool     $stop_on_first_occurance Whether to stop on fist occurance
     * 
     * @return Landclaim[] Array of landclaims containing given position. Empty array if no such landclaims were found
     */
    public function getLandclaimsByPosition(Position $position, bool $stop_on_first_occurance=false): array {
        $landclaims = array();
        foreach($this->landclaims as $land) {
            if ($land->containsPosition($position)) {
                array_push($landclaims, $land);
                if ($stop_on_first_occurance) {
                    break;
                }
            }
        }
        return $landclaims;
    }
    
    /**
     * Get an array of landclaims owned by specified owner
     * 
     * @param Player $player Whose landclaim list has to be returned
     * 
     * @return Landclaim[] Array of landclaims owned by specified owner. Empty array if has no landclaims
     */
    public function listLandOwnedBy(Player $player): array {
        $landclaims = array();
        foreach($this->landclaims as $land) {
            if ($land->isOwner($player)) {
                array_push($landclaims, $land);
            }
        }
        return $landclaims;
    }
    
    /**
     * Get an array of landclaims which itersect given landclaim
     * 
     * @param Landclaim $land                    Landclaim to check for intersection
     * @param bool      $stop_on_first_occurance Whether to stop on first occurance
     * 
     * @return Landclaim[] Array of lanclaims which intersect given landclaim. Empty array if no such landclaims were found
     */
    public function getIntersectingLandclaims(Landclaim $land, bool $stop_on_first_occurance=false): array {
        $landclaims = array();
        foreach($this->landclaims as $land2) {
            if ($land->intersectsLandclaim($land2)) {
                array_push($landclaims, $land2);
                if ($stop_on_first_occurance) {
                    break;
                }
            }
        }
        return $landclaims;
    }
}
