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

use SQLite3;

use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\math\Vector3;
use Ramsey\Uuid\Uuid;

use CerberusPM\Cerberus\Cerberus;
use CerberusPM\Cerberus\Landclaim;
use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\exception\LandExistsException;
use CerberusPM\Cerberus\exception\LandIntersectException;

use function is_null;
use function in_array;
use function array_map;
use function array_push;
use function array_keys;
use function implode;
use function explode;
use function count;
use function array_key_exists;

/**
 * Class for landclaim management
 */
class LandManager {
    private static LandManager $instance;
    
    private array $landclaims = [];
    public array $table_columns = ["name", "creator_uuid", "owner_uuids", "member_uuids", "pos1", "pos2", "world_name", "spawnpoint", "flags", "creation_timestamp"];
    
    private function __construct() {
        $lang_manager = LangManager::getInstance();
        // Load saved landclaims
        Cerberus::getInstance()->getLogger()->info($lang_manager->translate("plugin.loading_landclaims", include_prefix: false));
        $landclaim_count = $this->loadLandclaims();
        // Announce loaded landclaims
        Cerberus::getInstance()->getLogger()->info($lang_manager->translate("plugin.loaded_landclaims", [$landclaim_count], false));
    }
    
    /**
     * Get LandManager instance
     * 
     * @return LandManager LandManager instance
     */
    public static function getInstance(): LandManager {
        if (!isset(self::$instance)) {
            self::$instance = new LandManager();
        }
        
        return self::$instance;
    }
    
    /**
     * Loads all the landclaims from the db
     * 
     * @return int Loaded landclaims count
     */
    public function loadLandclaims(): int {
        $db = $this->openDB();
        
        $result = $db->query('SELECT * FROM landclaims');
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $pos1_array = explode(',', $row["pos1"]);
            $pos2_array = explode(',', $row["pos2"]);
            $pos1 = new Vector3((int) $pos1_array[0], (int) $pos1_array[1], (int) $pos1_array[2]);
            $pos2 = new Vector3((int) $pos2_array[0], (int) $pos2_array[1], (int) $pos2_array[2]);
            
            $land = new Landclaim($row["name"], Uuid::fromString($row["creator_uuid"]),
                    $pos1, $pos2, $row["world_name"], $row["creation_timestamp"]);
            // Set spawnpoint if exists
            if (!empty($row["spawnpoint"])) {
                $spoint_array = explode(',', $row["spawnpoint"]);
                $land->setSpawnpoint(new Vector3((float) $spoint_array[0], (float) $spoint_array[1], (float) $spoint_array[2]));
            }
            // Set owners and members
            if (!empty($row["owner_uuids"])) {
                $owners = array_map(fn($str) => Uuid::fromString($str), explode(',', $row["owner_uuids"]));
                $land->setOwnerUuids($owners);
            }
            if (!empty($row["member_uuids"])) {
                $members = array_map(fn($str) => Uuid::fromString($str), explode(',', $row["member_uuids"]));
                $land->setMemberUuids($members);
            }
            // Set flags
            if (!empty($row["flags"])) {
                $flag_ids = explode(',', $row["flags"]);
                foreach ($flag_ids as $flag_id) {
                    $land->addFlagById($flag_id);
                }
            }
            
            $this->landclaims[$row["name"]] = $land;
            $land->setRegistered(true);
        }
        $db->close();
        
        return count($this->landclaims);
    }
    
    /**
     * Reloads all the landclaims from the db
     * 
     * @return int Loaded landclaims count
     */
    public function reloadLandclaims(): int {
        $this->landclaims = array();
        return $this->loadLandclaims();
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
        
        $land->setRegistered(true);
        
        // Save to database
        $db = $this->openDB();
        // Convert all landclaim fields to SQL-saveable format
        $p1 = $land->getFirstPosition();
        $p2 = $land->getSecondPosition();
        $sp = $land->getSpawnpoint();

        $stmt = $db->prepare("INSERT OR REPLACE INTO landclaims (" . 
                implode(', ', $this->table_columns) . ") VALUES (:" . implode(', :', $this->table_columns) . ')');
        
        $stmt->bindValue(':name', $land_name, SQLITE3_TEXT);
        $stmt->bindValue(':creator_uuid', $land->getCreatorUuid()->toString(), SQLITE3_TEXT);
        $stmt->bindValue(':owner_uuids', $land->getOwnerUuidsString(), SQLITE3_TEXT);
        $stmt->bindValue(':member_uuids', $land->getMemberUuidsString(), SQLITE3_TEXT);
        $stmt->bindValue(':pos1', "{$p1->getX()},{$p1->getY()},{$p1->getZ()}", SQLITE3_TEXT);
        $stmt->bindValue(':pos2', "{$p2->getX()},{$p2->getY()},{$p2->getZ()}", SQLITE3_TEXT);
        $stmt->bindValue(':world_name', $land->getWorldName(), SQLITE3_TEXT);
        $stmt->bindValue(':spawnpoint', (is_null($land->getSpawnpoint())) ? NULL : "{$sp->getX()},{$sp->getY()},{$sp->getZ()}", SQLITE3_TEXT);
        $stmt->bindValue(":flags", $land->getFlagsString(), SQLITE3_TEXT);
        $stmt->bindValue(':creation_timestamp', $land->getCreationTimestamp(), SQLITE3_INTEGER);
        $stmt->execute();
        
        $db->close();
    }
    
    public function updateDBValueForLandclaim(string $land_name, string $column_name, string|int|NULL $new_value): void {
        if (!$this->exists($land_name)) {
            return;
        }
        if (!in_array($column_name, $this->table_columns)) {
            Throw new \CerberusPM\Cerberus\exception\IllegalDBColumnNameException();
        }
        $db = $this->openDB();
        $stmt = $db->prepare("UPDATE landclaims SET $column_name = :new_value WHERE name = :name");
        $stmt->bindValue(":name", $land_name, SQLITE3_TEXT);
        switch (gettype($new_value)) {
            case "string":
                $stmt->bindValue(":new_value", $new_value, SQLITE3_TEXT);
                break;
            case "integer":
                $stmt->bindValue(":new_value", $new_value, SQLITE3_INTEGER);
                break;
            case "NULL":
                $stmt->bindValue(":new_value", NULL, SQLITE3_NULL);
                break;
        }
        $stmt->execute();
        
        $db->close();
    }
    
    /**
     * Unregister (delete) a landclaim
     * 
     * @param string $land_name Name of a landclaim to unregister
     */
    public function unregisterLandclaim(string $land_name): void {
        if ($this->exists($land_name)) {
            unset($this->landclaims[$land_name]);
            // Save to database
            $db = $this->openDB();
            
            $stmt = $db->prepare("DELETE FROM landclaims WHERE name = :name");
            $stmt->bindValue(':name', $land_name, SQLITE3_TEXT);
            $stmt->execute();
            
            $db->close();
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
        return array_key_exists($land_name, $this->landclaims);
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
        $this->registerLandclaim($land);
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
    
    private function openDB(): SQLite3 {
        $db = new SQLite3(Cerberus::getInstance()->getDataFolder() . 'landclaims.db');
        // Create the necessary table if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS landclaims ("
                . "name TEXT PRIMARY KEY, creator_uuid TEXT NOT NULL, "
                . "owner_uuids TEXT, member_uuids TEXT, "
                . "pos1 TEXT NOT NULL, pos2 TEXT NOT NULL, "
                . "world_name TEXT NOT NULL, spawnpoint TEXT, flags TEXT,"
                . "creation_timestamp INTEGER NOT NULL)");
        return $db;
    }
}
