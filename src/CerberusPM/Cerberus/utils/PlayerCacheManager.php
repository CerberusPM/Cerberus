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

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

use CerberusPM\Cerberus\Cerberus;

use function array_key_exists;
use function str_starts_with;
use function strtolower;

/**
 * A player-caching system to associate player UUIDS with last used names
 */
class PlayerCacheManager {
    private static PlayerCacheManager $instance;
    
    private array $uuid_name_map = [];
    
    private function __construct() {
        $this->loadCache();
    }
    
    /**
     * Get PlayerCacheManager instance
     * 
     * @return PlayerCacheManager PlayerCacheManager instance
     */
    public static function getInstance(): PlayerCacheManager {
        if (!isset(self::$instance)) {
            self::$instance = new PlayerCacheManager();
        }
        
        return self::$instance;
    }
    
    public function getNameByUUID(String|UuidInterface $uuid): String|null {
       if ($uuid instanceof UuidInterface) {
           $uuid = $uuid->toString();
       }
       if (array_key_exists($uuid, $this->uuid_name_map)) {
            return $this->uuid_name_map[$uuid];
       }
       return null;
    }
    
    public function getUUIDByName(String $name): UuidInterface|null {
        $name = strtolower($name);
        foreach ($this->uuid_name_map as $uuid => $player_name) {
            if (str_starts_with(strtolower($player_name), $name)) {
                return Uuid::fromString($uuid);
            }
        }
        return null;
    }
    
    public function setUuidByName(String $name, UuidInterface|String $uuid): void {
        if ($uuid instanceof UuidInterface) {
           $uuid = $uuid->toString();
        }
        $this->uuid_name_map[$uuid] = $name;
       
        $db = $this->openDB();
        
        $stmt = $db->prepare("INSERT OR REPLACE INTO player_uuid_cache (uuid, last_seen_name)"
                . " VALUES (:uuid, :last_seen_name)");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->bindValue(':last_seen_name', $name, SQLITE3_TEXT);
        $stmt->execute();
        $db->close();
    }
    
    
    private function loadCache(): void {
        $db = $this->openDB();
        
        $result = $db->query('SELECT * FROM player_uuid_cache');
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $this->uuid_name_map[$row["uuid"]] = $row["last_seen_name"];
        }
        
        $db->close();
    }
    
    private function openDB(): SQLite3 {
        $db = new SQLite3(Cerberus::getInstance()->getDataFolder() . 'landclaims.db');
        // Create the necessary table if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS player_uuid_cache ("
                . "uuid TEXT PRIMARY KEY, last_seen_name TEXT NOT NULL)");
        return $db;
    }
    
    
}
