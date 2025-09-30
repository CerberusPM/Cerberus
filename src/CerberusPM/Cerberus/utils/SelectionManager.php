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

namespace CerberusPM\Cerberus\utils;

use pocketmine\world\Position;
use pocketmine\player\Player;

use function array_key_exists;

/**
 * A class for selection management
 */
class SelectionManager {
    /** @var array $selectingFirstPosition Stores first positions and who've set them. */
    private static array $selectingFirstPosition = [];
    /** @var array $selectingFirstPosition Stores second positions and who've set them. */
    private static array $selectingSecondPosition = [];
    
    private function __construct() { }
    
    /**
     * Select the first position
     * 
     * @param Player   $selector Who selects the position
     * @param Position $position Position in the world to be set as the first position
     * 
     */
    public static function selectFirstPosition(Player $selector, Position $position): void {
        $uuid = $selector->getUniqueId()->toString();
        self::$selectingFirstPosition[$uuid] = $position;
    }
    
    /**
     * Select the second position
     * 
     * @param Player   $selector Who selects the position
     * @param Position $position Position in the world to be set as the second position
     */
    public static function selectSecondPosition(Player $selector, Position $position): void {
        $uuid = $selector->getUniqueId()->toString();
        self::$selectingSecondPosition[$uuid] = $position;
    }
    
    /**
     * Unset previously selected first position
     * 
     * @param Player $selector Whose first position selection has to be unset
     */
    public static function deselectFirstPosition(Player $selector): void {
        unset(self::$selectingFirstPosition[$selector->getUniqueId()->toString()]);
    }
    
    /**
     * Unset previously selected second position
     * 
     * @param Player $selector Whose first position selection has to be unset
     */
    public static function deselectSecondPosition(Player $selector): void {
        unset(self::$selectingSecondPosition[$selector->getUniqueId()->toString()]);
    }
    
    /**
     * Unset all selected positions of $selector
     * 
     * @param Player $selector Whose first and seconf position selection has to be cleared
     */
    public static function deselectAll(Player $selector): void {
        self::deselectFirstPosition($selector);
        self::deselectSecondPosition($selector);
    }
    
    /**
     * Check by name if has selected any positions
     * 
     * @param Player $selector Whose presence of any position selection has to be checked 
     * 
     * @return bool True if any position has been selected by the given name, false if not
     */
    public static function hasSelected(Player $selector): bool {
        $uuid = $selector->getUniqueId()->toString();
        return self::hasSelectedFirst($selector) || self::hasSelectedSecond($selector);
    }
    
    /**
     * Check by name if has selected first position
     * 
     * @param Player $selector Whose first position selection has to be checked
     * 
     * @return bool True if first position has been selected, false if not.
     */
    public static function hasSelectedFirst(Player $selector): bool {
        return array_key_exists($selector->getUniqueId()->toString(), self::$selectingFirstPosition);
    }
    
    /**
     * Check by name if has selected second position
     * 
     * @param Player $selector Exact name of whose second position selection has to be checked
     * 
     * @return bool True if second position has been selected, false if not.
     */
    public static function hasSelectedSecond(Player $selector): bool {
        return array_key_exists($selector->getUniqueId()->toString(), self::$selectingSecondPosition);
    }
    
    /**
     * Get the first position selected by $selector
     * 
     * @param Player $selector Exact name of whose first selection position is to be get
     * 
     * @return Position|null Returns pocketmine\World\Position or null if position is not selected
     */
    public static function getSelectedFirstPosition(Player $selector): Position | null {
        $uuid = $selector->getUniqueId()->toString();
        if (array_key_exists($uuid, self::$selectingFirstPosition)) {
            return self::$selectingFirstPosition[$uuid];
        } else {
            return null;
        }
    }
    
    /**
     * Get the second position selected by $selector
     * 
     * @param Player $selector Exact name of whose second selection position is to be get
     * 
     * @return Position|null Returns pocketmine\World\Position or null if position is not selected
     */
    public static function getSelectedSecondPosition(Player $selector): Position | null {
        $uuid = $selector->getUniqueId()->toString();
        if (array_key_exists($uuid, self::$selectingSecondPosition)) {
            return self::$selectingSecondPosition[$uuid];
        } else {
            return null;
        }
    }
    
}
