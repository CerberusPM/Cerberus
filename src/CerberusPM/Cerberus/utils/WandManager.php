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

use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use pocketmine\item\Item;
use pocketmine\item\Durable;
use pocketmine\item\StringToItemParser;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\nbt\tag\CompoundTag;

use CerberusPM\Cerberus\utils\ConfigManager;
use CerberusPM\Cerberus\utils\LangManager;

use CerberusPM\Cerberus\exception\InventoryFullException;

use function is_array;

/**
 * A class responsible for wand giving and checking
 */
class WandManager {
    
    public const TAG_CERBERUS = "Cerberus";
    public const TAG_WAND = "isWand";
    
    /**
     * Give player a wand
     * 
     * @param Player $player Player who will receive a wand
     * 
     * @throws InventoryFullException if inventory is full and there's no place for wand
     */
    public static function giveWand(Player $player): void {
        $config_manager = ConfigManager::getInstance();
        $language_manager = LangManager::getInstance();
        
        
        //Construct the wand item
        $wand_id = $config_manager->get("wand-item");
        $wand_item = StringToItemParser::getInstance()->parse($wand_id) ?? LegacyStringToItemParser::getInstance()->parse($wand_id);
        if ($wand_item instanceof Durable) {
            $wand_item->setUnbreakable(true);
        }
        // Set custom name
        if ($config_manager->get("wand-use-default-name")) {
            $wand_name = $language_manager->translate("wand.name", include_prefix: false);
        } // LangManager returns already colorized string
        else {
            $wand_name = $config_manager->get("wand-name", false);
        } // Don't use the value from default config as the one from language file will be used
        if (!empty($wand_name)) {
            $wand_item->setCustomName(TextFormat::colorize($wand_name));
        } else { //Looks like name option is left blank. Applying the default name
            $wand_item->setCustomName($language_manager->translate("wand.name", include_prefix: false));
        }
        // Set lore
        $lore_already_colorized = false;
        if ($config_manager->get("wand-use-default-lore")) {
            $wand_lore = $language_manager->translate("wand.lore", include_prefix: false);
            $lore_already_colorized = true;
        } else {
            $wand_lore = $config_manager->get("wand-lore", false);
        }

        if (!empty($wand_lore)) {
            if (!is_array($wand_lore)) { // If lore is string, convert to array with one element since Item->setLore() requieres an array of strings
                $wand_lore = array($wand_lore);
            }
            if (!$lore_already_colorized) {
                foreach ($wand_lore as &$lore_string) {
                    $lore_string = TextFormat::colorize($lore_string);
                }
                unset($lore_string);
            }
            $wand_item->setLore($wand_lore);
        }
        //Set enchantments
        $wand_enchantments = $config_manager->get("wand-enchantments");
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
    public static function isWand(Item $item): bool {
        if ($item->hasNamedTag()) {
            $wand_tag = $item->getNamedTag()->getCompoundTag(self::TAG_CERBERUS)->getTag(self::TAG_WAND);
            if (isset($wand_tag) && $wand_tag->getValue() == 1) {
                return true;
            }
        }
        return false;
    }
}
