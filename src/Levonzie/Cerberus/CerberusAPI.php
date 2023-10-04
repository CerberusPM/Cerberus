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

use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use pocketmine\item\Item;
use pocketmine\item\Durable;
use pocketmine\item\StringToItemParser;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\world\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\math\Vector3;

use Levonzie\Cerberus\Cerberus;
use Levonzie\Cerberus\utils\ConfigManager;
use Levonzie\Cerberus\utils\LangManager;
use Levonzie\Cerberus\utils\LandManager;
use Levonzie\Cerberus\exception\InventoryFullException;

use function is_array;

/**
 * An API class which provides all necessary land management methods used by subcommands
 */

class CerberusAPI { 
    private static CerberusAPI $instance;
    private Cerberus $plugin;
    
    private $version = "1.0.0-DEV";
    
    public const TAG_CERBERUS = "Cerberus";
    public const TAG_WAND = "isWand";
    
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
    
    /**
     * Give player a wand
     * 
     * @param Player $player Player who will receive a wand
     * 
     * @throws InventoryFullException if inventory is full and there's no place for wand
     */
    public function giveWand(Player $player): void {
        $this->config_manager = ConfigManager::getInstance();
        $this->language_manager = LangManager::getInstance();
        
        //Construct the wand item
        $wand_id = $this->config_manager->get("wand-item");
        $wand_item = StringToItemParser::getInstance()->parse($wand_id) ?? LegacyStringToItemParser::getInstance()->parse($wand_id);
        if ($wand_item instanceof Durable)
            $wand_item->setUnbreakable(true);
        // Set custom name
        if ($this->config_manager->get("wand-use-default-name"))
            $wand_name = $this->language_manager->translate("wand.name"); // LangManager returns already colorized string
        else
            $wand_name = $this->config_manager->get("wand-name", false); // Don't use the value from default config as the one from language file will be used
        if (!empty($wand_name))
            $wand_item->setCustomName(TextFormat::colorize($wand_name));
        else //Looks like name option is left blank. Applying the default name
            $wand_item->setCustomName($this->language_manager->translate("wand.name"));
        // Set lore
        $lore_already_colorized = false;
        if ($this->config_manager->get("wand-use-default-lore")) {
            $wand_lore = $this->language_manager->translate("wand.lore");
            $lore_already_colorized = true;
        } else
            $wand_lore = $this->config_manager->get("wand-lore", false);
        
        if (!empty($wand_lore)) {
            if (!is_array($wand_lore)) // If lore is string, convert to array with one element since Item->setLore() requieres an array of strings
                $wand_lore = array($wand_lore);
            if (!$lore_already_colorized) {
                foreach ($wand_lore as &$lore_string)
                    $lore_string = TextFormat::colorize($lore_string);
                unset($lore_string);
            }
            $wand_item->setLore($wand_lore);
        }
        //Set enchantments
        $wand_enchantments = $this->config_manager->get("wand-enchantments");
        if (is_array($wand_enchantments)) {
            foreach ($wand_enchantments as $ench_name => $ench_lvl) {
                $ench = StringToEnchantmentParser::getInstance()->parse($ench_name);
                if (!empty($ench)) {
                    $ench_instance = new EnchantmentInstance($ench, $ench_lvl);
                    $wand_item->addEnchantment($ench_instance);
                }
            }
        }
        // Set NBT
        $cerberus_compound_tag = CompoundTag::create();
        $cerberus_compound_tag->setByte(self::TAG_WAND, 1); //This NBT tag makes wand a wand
        $wand_item->getNamedTag()->setTag(self::TAG_CERBERUS, $cerberus_compound_tag);
        //Give the item
        $player_inv = $player->getInventory();
        $selected_item = $player_inv->getItemInHand(); // Retreive currently held item, so that it'll be possible to move it to a different slot
        if ($player_inv->canAddItem($wand_item)) { // Check if inventory is full
            $player_inv->setItemInHand($wand_item); // Replace currently held item (or air) with the wand item
            $player_inv->addItem($selected_item); // Return previously held item to player by adding it to an available empty slot
        } else {
            Throw new InventoryFullException("Inventory is full");
        }
    }
    
    /**
     * Check whether item is wand
     * 
     * @param Item $item An item for wand check
     *                   Turned off by default - any item with isWand NBT tag are considered to be a wand.
     * 
     * @return bool True if is wand, false if is not a wand
     */
    public function isWand(Item $item): bool {
        if ($item->hasNamedTag()) {
            $wand_tag = $item->getNamedTag()->getCompoundTag(self::TAG_CERBERUS)->getTag(self::TAG_WAND);
            if (isset($wand_tag) && $wand_tag->getValue() == 1)
                return true;
        }
        return false;
    }
    
    /**
     * Create a landclaim
     * 
     * @param string  $land_name  Name of the landclaim (should be unique)
     * @param string  $land_owner Landclaim owner name (who this landclaim will belong to)
     * @param Vector3 $pos1       First position of the landclaim
     * @param Vector3 $pos2       Second position of the landclaim
     * @param string  $world_name Folder name of the world, where the landclaim will be created
     *
     * @throws LandExistsException if a landclaim with given $land_name already exists  
     */
    public function createLand(string $land_name, string $land_owner, Vector3 $pos1, Vector3 $pos2, string $world_name): void {
        LandManager::registerLandclaim(new Landclaim($land_name, $land_owner, $pos1, $pos2, $world_name));
    }
    
    /**
     * Get a landclaim which contains given position or null if it there's no such landclaim
     * 
     * @param Position $position Position to be checked for inclusion in a landclaim
     * 
     * @return Landclaim|null Landclaim if a landclaim, containing given position exists; null if there's no landclaim containing given position
     */
    public function getLandByPosition(Position $position): Landclaim|null {
        foreach(LandManager::getLandclaims() as $land) {
            if ($land->containsPosition($position))
                return $land;
        }
        return null;
    }
    
    /**
     * Get landclaim with given name or null if it doesn't exist
     * 
     * @param string $land_name Name of a landclaim to get.
     * 
     * @return Landclaim|null Landclaim if exists, null if doesn't exist
     */
    public function getLandByName(string $land_name): Landclaim|null {
        if (LandManager::exists($land_name))
            return LandManager::getLandclaims()[$land_name];
        else
            return null;
    }
    
    /**
     * Check if landclaim with given name exists
     * 
     * @param string $land_name Land name to be checked for existance
     * 
     * @return bool Whether land exists or not
     */
    public function landExists(string $land_name): bool {
        return LandManager::exists($land_name);
    }
    
}
