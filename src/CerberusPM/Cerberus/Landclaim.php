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

use DateTime;
use DateTimeZone;

use pocketmine\utils\Timezone;
use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\player\OfflinePlayer;
use Ramsey\Uuid\UuidInterface;

use CerberusPM\Cerberus\Cerberus;
use CerberusPM\Cerberus\utils\ConfigManager;
use CerberusPM\Cerberus\utils\LandManager;

use function min;
use function max;
use function time;
use function strval;
use function is_null;
use function in_array;
use function implode;
use function array_push;
use function array_map;

class Landclaim {
    protected string $name;
    protected UuidInterface $creator;
    protected $owners = [];
    protected $members = [];
    protected Vector3 $pos1;
    protected Vector3 $pos2;
    protected string $world_name;
    protected Vector3 $spawn_point;
    protected int $creation_timestamp;
    protected bool $registered = false;
    

    public function __construct(string $name, Player|UuidInterface $player, Vector3 $pos1, Vector3 $pos2, string $world_name, int $creation_timestamp = null) {
        $this->name = $name;
        if ($player instanceof Player) {
            $this->creator = $player->getUniqueId();
            $this->addOwner($player); // The initial creator can then remove themselves from the list
        } elseif ($player instanceof UuidInterface) {
            $this->creator = $player;
            array_push($this->owners, $player);
        }
        
        //Optimize positions for containsPosition() calculation speed boost
        $this->pos1 = Vector3::minComponents($pos1, $pos2);
        $this->pos2 = Vector3::maxComponents($pos1, $pos2);
        $this->world_name = $world_name;
        if (empty($creation_timestamp)) { // Let LandManager override it when loading from db
            $this->creation_timestamp = time();
        } else {
            $this->creation_timestamp = $creation_timestamp;
        }
    }
    
    /**
     * @return string Landclaim name
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * @return string Lanclaim creator name
     */
    public function getCreatorName(): string {
        return Cerberus::getInstance()->getServer()->getPlayerByUUID($this->creator)->getDisplayName();
    }

    /**
     * @return UuidInterface Lanclaim creator UUID
     */
    public function getCreatorUuid(): UuidInterface {
        return $this->creator;
    }
    
    /**
     * @return Vector3 Landclaim's first position
     */
    public function getFirstPosition(): Vector3 {
        return $this->pos1;
    }
    
    /**
     * @return Vector3 Landclaim's second position
     */
    public function getSecondPosition(): Vector3 {
        return $this->pos2;
    }
    
    /**
     * @return string Name of the world where landclaim is located
     */
    public function getWorldName(): string {
        return $this->world_name;
    }
    
    /**
     * @return Vector3|null Vector3 of spawnpoint coordinates if they are set and null if they aren't
     */
    public function getSpawnpoint(): Vector3|null {
        if (!isset($this->spawn_point)) {
            return null;
        }
        return $this->spawn_point;
    }

    /**
     * Get array of players with owner permissions
     *
     * @return UuidInterface[] array of UUIDS of players with owner permissions
     */
    public function getOwnerUuids(): array {
        return $this->owners;
    }
    
    /**
     * Get array of players as a comma-separated string
     *
     * @return Comma-separated string of UUIDS of players with owner permissions
     */
    public function getOwnerUuidsString(): string {
        return implode(',', array_map(fn($uuid) => $uuid->toString(), $this->owners));
    }
    
    /**
     * Get array of players as a comma-separated string
     *
     * @return Comma-separated string of UUIDS of players with owner permissions
     */
    public function getMemberUuidsString(): string {
        return implode(',', array_map(fn($uuid) => $uuid->toString(), $this->members));
    }

    /**
     * Get array of players with member permissions
     *
     * @return UuidInterface[] Array of UUIDS of players with owner permissions
     */
    public function getMemberUuids(): array {
        return $this->members;
    }

    /**
     * Get array of players with owner permissions
     *
     * @return string[] Array of names of players with owner permissions
     */
    public function getOwnerNames(): array {
        return array_map(fn($pl) => Cerberus::getInstance()->getServer()->getPlayerByUUID($pl)->getDisplayName(), $this->owners);
    }

    /**
     * Get array of players with member permissions
     *
     * @return string[] Array of names of players with member permissions
     */
    public function getMemberNames(): array {
        return array_map(fn($pl) => Cerberus::getInstance()->getServer()->getPlayerByUUID($pl)->getDisplayName(), $this->members);
    }
    
    /**
     * Sets spawnpoint for the landclaim
     */
    public function setSpawnpoint(Vector3 $pos): void {
        $this->spawn_point = $pos;
        // Update spawnpoint in the db
        if ($this->isRegistered()) {
            LandManager::getInstance()->updateDBValueForLandclaim($this->name, "spawnpoint", "{$pos->getX()},{$pos->getY()},{$pos->getZ()}");
        }
    }
    
    /**
     * Unsets spawnpoint for the landclaim
     */
    public function unsetSpawnpoint(): void {
        unset($this->spawn_point);
        // Update spawnpoint in the db
        if ($this->isRegistered()) {
            LandManager::getInstance()->updateDBValueForLandclaim($this->name, "spawnpoint", NULL);
        }
    }

    /**
     * Add player to owner list
     * 
     * @param Player player A player to add
     */
    public function addOwner(Player|OfflinePlayer $player): bool {
        if (!isset($player) || ($player instanceof OfflinePlayer && !$player->hasPlayedBefore())) {
            return false;
        }
        if (!in_array($player->getUniqueId(), $this->owners)) {
            array_push($this->owners, $player->getUniqueId());
            // Update owners in the db
            if ($this->isRegistered()) {
                LandManager::getInstance()->updateDBValueForLandclaim($this->name, "owner_uuids", $this->getOwnerUuidsString());
            }
        }
        return true;
    }

    /**
     * Add player to member list
     * 
     * @param Player player A player to add
     */
    public function addMember(Player|OfflinePlayer $player): bool {
        if (!isset($player) || ($player instanceof OfflinePlayer && !$player->hasPlayedBefore())) {
            return false;
        }
        if (!in_array($player->getUniqueId(), $this->members)) {
            array_push($this->members, $player->getUniqueId());
            // Update members in the db
            if ($this->isRegistered()) {
                LandManager::getInstance()->updateDBValueForLandclaim($this->name, "member_uuids", $this->getMemberUuidsString());
            }
        }
        return true;
    }
    
    /**
     * Set the owner UUID array
     * 
     * @param array $uuids Array of owner UUIDs
     * @return void
     */
    public function setOwnerUuids(array $uuids): void {
        $this->owners = $uuids;
    }
    
    /**
     * Set the member UUID array
     * 
     * @param array $uuids Array of member UUIDs
     * @return void
     */
    public function setMemberUuids(array $uuids): void {
        $this->members = $uuids;
    }
    
     /**
     * Remove player from the owner list
     * 
     * @param Player player A player to remove
     */
    public function removeOwner(Player $player): void {
        if (($key = array_search($player->getUniqueId(), $this->owners)) !== false) {
            unset($this->owners[$key]);
            // Update owners in the db
            if ($this->isRegistered()) {
                LandManager::getInstance()->updateDBValueForLandclaim($this->name, "owner_uuids", $this->getOwnerUuidsString());
            }
        }
    }

    /**
     * Remove player from the member list
     * 
     * @param Player player A player to remove
     */
    public function removeMember(Player $player): void {
        if (($key = array_search($player->getUniqueId(), $this->members)) !== false) {
            unset($this->members[$key]);
            // Update members in the db
            if ($this->isRegistered()) {
                LandManager::getInstance()->updateDBValueForLandclaim($this->name, "member_uuids", $this->getMemberUuidsString());
            }
        }
    }
    
    /**
     * Check if player is owner
     * 
     * @param Player player A player to check for ownership permission
     */
    public function isOwner(Player $player): bool {
        return in_array($player->getUniqueId(), $this->owners);
    }
    
     /**
     * Check if player is member
     * 
     * @param Player player A player to check for membership
     */
    public function isMember(Player $player): bool {
        return in_array($player->getUniqueId(), $this->members);
    }
    
    /**
     * Check if this landclaim is registered in LandManager
     * 
     * @return bool Registered in LandManager or not
     */
    public function isRegistered(): bool {
        return $this->registered;
    }
    
    /**
     * Mark this landclaim as registered in LandManager.
     * This method is usually executed by LandManager after the landclaim has been added to its landclaim array
     * 
     * @param bool $value Set registered in LandManager or not
     * @return void
     */
    public function setRegistered(bool $value): void {
        $this->registered = $value;
    }

    /**
     * Check whether landclaim contains a position
     * 
     * @param Position $pos Position to be checked for inclusion in landclaim
     * 
     * @return bool Whether landclaim contains given position or not
     */
    public function containsPosition(Position $pos): bool {
        if ($pos->getWorld()->getFolderName() === $this->getWorldName() &&
                $pos->getX() >= $this->getFirstPosition()->getX() &&
                $pos->getFloorX() <= $this->getSecondPosition()->getX() &&
                $pos->getY() >= $this->getFirstPosition()->getY() &&
                $pos->getFloorY() <= $this->getSecondPosition()->getY() &&
                $pos->getZ() >= $this->getFirstPosition()->getZ() &&
                $pos->getFloorZ() <= $this->getSecondPosition()->getZ()) {
            return true;
        }
        return false;
    }
    
    /**
     * Check whether landclaim intersects another landclaim
     * 
     * @param Landclaim $target A landclaim to check for intersection with this landclaim
     * 
     * @return bool True if landclaims intersect, false if not
     */
    public function intersectsLandclaim(Landclaim $target): bool {
        if ($target->getWorldName() === $this->getWorldName() &&
                $target->getFirstPosition()->getX() <= $this->getSecondPosition()->getX() &&
                $target->getSecondPosition()->getX() >= $this->getFirstPosition()->getX() &&
                $target->getFirstPosition()->getY() <= $this->getSecondPosition()->getY() &&
                $target->getSecondPosition()->getY() >= $this->getFirstPosition()->getY() &&
                $target->getFirstPosition()->getZ() <= $this->getSecondPosition()->getZ() &&
                $target->getSecondPosition()->getZ() >= $this->getFirstPosition()->getZ()) {
            return true;
        }
        return false;
    }
    
    /**
     * @return int Length of the landclaim
     */
    public function getLength(): int {
        $side1_len = $this->getSecondPosition()->getX() - $this->getFirstPosition()->getX();
        $side2_len = $this->getSecondPosition()->getZ() - $this->getFirstPosition()->getZ();
        return max($side1_len, $side2_len)+1; //Add one, as the edge block should be count
    }

    /**
     * @return int Width of the landclaim
     */
    public function getWidth(): int {
        $side1_len = $this->getSecondPosition()->getX() - $this->getFirstPosition()->getX();
        $side2_len = $this->getSecondPosition()->getZ() - $this->getFirstPosition()->getZ();
        return min($side1_len, $side2_len)+1; //Add one, as the edge block should be count
    }

    /**
     * @return int Height of the landclaim
     */
    public function getHeight(): int {
        return $this->getSecondPosition()->getY() - $this->getFirstPosition()->getY()+1;
    }

    /**
     * @return int Area of the landclaim
     */
    public function getArea(): int {
        return $this->getWidth() * $this->getLength();
    }

    /**
     * @return int Volume of the landclaim
     */
    public function getVolume(): int {
        return $this->getLength() * $this->getWidth() * $this->getHeight();
    }
    
    /**
     * @return int Creation time Unix timestamp (GMT)
     */
    public function getCreationTimestamp(): int {
        return $this->creation_timestamp;
    }
    
    /**
     * Get human-readable landclaim creation date string, formatted with respect to the timezone
     * 
     * @param string $format   PHP DateTime formatting string. If null, gets the format from the config option date-format
     * @param string $timezone Timezone in PHP DateTimeZone format. If null, gets the timezone from config option default-timezone.
     *                         Tries to figure system timezone automatically if config option is not set
     * 
     * @see https://www.php.net/manual/en/timezones.php                                           List of available timezones
     * @see https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters Format parameters
     * 
     * @return string Formatted date string
     */
    public function getFormattedCreationDate(string $format=null, string $timezone=null): string {
        $timestamp = $this->getCreationTimestamp();
        if (is_null($format)) {
            $format = ConfigManager::getInstance()->get("date-format") ?? "Y-m-d H:i:s";
        }
        if (is_null($timezone)) {
            $timezone = ConfigManager::getInstance()->get("default-timezone"); //Try to get timezone from the config
            if (empty($timezone)) { //Figure out server time zone if it's not set
                $timezone = Timezone::detectSystemTimezone();
            } // Not always accurate. Returned Africa/Juba for me, which is UTC+2, though I had UTC+3 set on my system
        }
        $datetime = new DateTime('@' . strval($timestamp), new DateTimeZone('UTC')); //Timestamp is stored in UTC to make timezone conversion easier
        $tz = new DateTimeZone($timezone);
        $datetime->setTimeZone($tz);
        return $datetime->format($format);
    }
}
