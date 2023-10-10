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

namespace Levonzie\Cerberus\command\subcommand;

use pocketmine\command\CommandSender;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\RawStringArgument;

use Levonzie\Cerberus\CerberusAPI;
use Levonzie\Cerberus\utils\SelectionManager;
use Levonzie\Cerberus\utils\ConfigManager;
use Levonzie\Cerberus\utils\LangManager;
use Levonzie\Cerberus\utils\LandManager;
use Levonzie\Cerberus\Landclaim;

use function is_null;
use function array_push;
use function count;
use function trim;
use function strval;

class ClaimSubcommand extends BaseSubCommand {
    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("name")); //Name of a landclaim
        
        $this->setPermission("cerberus.command.claim");
        
        $this->api = CerberusAPI::getInstance();
        $this->config_manager = ConfigManager::getInstance();
        $this->lang_manager = LangManager::getInstance();
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        $selector = $sender->getName();
        //Check if all positions are selected 
        if (!SelectionManager::hasSelectedFirst($selector) && !SelectionManager::hasSelectedSecond($selector)) {
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.select_both_positions"));
            return;
        }
        elseif (!SelectionManager::hasSelectedFirst($selector)) {
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.select_pos1"));
            return;
        }
        elseif (!SelectionManager::hasSelectedSecond($selector)) {
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.select_pos2"));
            return;
        }
        else {
            $pos1 = SelectionManager::getSelectedFirstPosition($selector);
            $pos2 = SelectionManager::getSelectedSecondPosition($selector);
        }
        //Check if positions are located in the same world
        if ($pos1[1] != $pos2[1]) {
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.world_mismatch"));
            return;
        } else {
            $world = $pos1[1];
        }
        //TODO: claim limit permission
        //Check if land already exists
        if ($this->api->landExists($args["name"])) {
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.already_exists", [$args["name"]]));
            return;
        }
        $new_land = new Landclaim($args["name"], $selector, $pos1[0], $pos2[0], $world);
        //Check if intersects land owned by somebody else
        $intersecting_landclaims = $this->api->getIntersectingLandclaims($new_land);
        if (!empty($intersecting_landclaims)) {
            $owned_by_somebody_else = array();
            foreach ($intersecting_landclaims as $land) { //Make a list of intersecting landclaims owned by other player
                if ($land->getOwner() != $selector)
                    array_push($owned_by_somebody_else, $land);
            }
            if (!empty($owned_by_somebody_else)) { //We allow to intersect landclaims owned by command executer themself
                if (count($owned_by_somebody_else) == 1) { //Intersects only one land. Sending appropriate messages
                    if (!$sender->hasPermission("cerberus.command.claim.bypass_intersect")) {
                        $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.intersects", [$owned_by_somebody_else[0]->getName(),
                                                                                                                                              $owned_by_somebody_else[0]->getOwner()]));
                        return;
                    } else {
                        $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.intersects.notification", [$owned_by_somebody_else[0]->getName(),
                                                                                                                                                           $owned_by_somebody_else[0]->getOwner()]));
                    }
                } else { //Intersects multiple landclaims. We should provide command executor a list
                    if (!$sender->hasPermission("cerberus.command.claim.bypass_intersect")) {
                        $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.intersects.multiple"));
                        foreach ($owned_by_somebody_else as $index => $land)
                            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.intersects.multiple.land_list_item", [strval($index+1) . ". ", $land->getName(), $land->getOwner()]));
                        return;
                    } else {
                        $inline_land_list_message = "";
                        foreach ($owned_by_somebody_else as $index => $land) {//Constructing a beautiful list of intersecting landclaims
                            if ($index+1 == count($owned_by_somebody_else)) //Last array item
                                $trailing_symbol = '';
                            elseif ($index == count($owned_by_somebody_else)-2) //Symbol before last
                                $trailing_symbol = ' ' . $this->lang_manager->translate("misc.and") . ' ';
                            else
                                $trailing_symbol = ", ";
                            $inline_land_list_message .= $this->lang_manager->translate("command.claim.intersects.multiple.inline_land_list_item", [$land->getName(), $land->getOwner()]) . $trailing_symbol;
                        //$inline_land_list_message = trim($inline_land_list_message); //Remove extra space at the end of the enlisting message
                        }
                        $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.intersects.multiple.notification", [$inline_land_list_message]));
                    }
                }
            }
        }
        //Finally create a landclaim
        LandManager::registerLandclaim($new_land);
        SelectionManager::deselectAll($selector);
        
        $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.success", [$args["name"]]));
    }
}
