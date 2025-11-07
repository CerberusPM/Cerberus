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

namespace CerberusPM\Cerberus\command\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\world\World;
use pocketmine\world\Position;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\IntegerArgument;

use CerberusPM\Cerberus\utils\ConfigManager;
use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\utils\SelectionManager;

use function min;
use function max;

class ExpandSubcommand extends CerberusSubcommand {
    
    private ConfigManager $config_manager;
    private LangManager $lang_manager;

    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("direction")); // up/down/both/front/back/left/right/...
        $this->registerArgument(1, new IntegerArgument("blocks", true)); //Optional. If not provided, expands to fit all blocks, available at a given direction
        
        $this->setPermission("cerberus.command.selection");
        
        $this->config_manager = ConfigManager::getInstance();
        $this->lang_manager = LangManager::getInstance();
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        //Check whether selection has been made
        if (!SelectionManager::hasSelected($sender)) {
            $sender->sendMessage($this->lang_manager->translate("command.expand.select_both_positions"));
            return;
        }
        if (!SelectionManager::hasSelectedFirst($sender)) {
            $sender->sendMessage($this->lang_manager->translate("command.expand.select_pos1"));
            return;
        }
        if (!SelectionManager::hasSelectedSecond($sender)) {
            $sender->sendMessage($this->lang_manager->translate("command.expand.select_pos2"));
            return;
        }
        $position1 = SelectionManager::getSelectedFirstPosition($sender);
        $position2 = SelectionManager::getSelectedSecondPosition($sender);
        //Check if positions are located in the same world
        if ($position1->getWorld() != $position2->getWorld()) {
            $sender->sendMessage($this->lang_manager->translate("command.expand.world_mismatch"));
            return;
        }
        $world = $position1->getWorld(); //We'll have to construct Position later, so the world object itself is needed
        if (!isset($world)) { //World not found
            $sender->sendMessage($this->lang_manager->translate("command.expand.world_not_found", [$position->getWorld()->getDisplayName()]));
            return;
        }
        //Get minimum and maximum position (by Y) to simplify further calculations
        if ($position1->getY() >= $position2->getY()) {
            $pos2 = $position1;
            $pos1 = $position2;
        } else {
            $pos2 = $position2;
            $pos1 = $position1;
        }
        //Act according to specified direction
        switch ($args["direction"]) {
            case "up":
            case "u":
                if (isset($args["blocks"])) {
                    $new_y = max(min($pos2->getY() + $args["blocks"], World::Y_MAX), World::Y_MIN);
                } else {
                    $new_y = World::Y_MAX;
                } //Expand all the way up by if block count is not specified
                
                SelectionManager::selectSecondPosition($sender,
                        new Position($pos2->getX(), $new_y, $pos2->getZ(), $world)); //Reselect the position
                SelectionManager::selectFirstPosition($sender, $pos1);
                $expansion_diff = $new_y - $pos2->getY(); //Find out by how many blocks was the selection expanded
                $sender->sendMessage($this->lang_manager->translate("command.expand.success.up", [$expansion_diff, $new_y]));
                break;
            case "down":
            case "d":
                if (isset($args["blocks"])) {
                    $new_y = min(max($pos1->getY() - $args["blocks"], World::Y_MIN), World::Y_MAX);
                } else {
                    $new_y = World::Y_MIN;
                } //Expand all the way down
                    
                SelectionManager::selectFirstPosition($sender,
                        new Position($pos1->getX(), $new_y, $pos1->getZ(), $world));
                SelectionManager::selectSecondPosition($sender, $pos2);
                $expansion_diff = $pos1->getY() - $new_y;
                $sender->sendMessage($this->lang_manager->translate("command.expand.success.down", [$expansion_diff, $new_y]));
                break;
            case "both":
            case "vert":
            case "b":
            case "v":
                if (isset($args["blocks"])) {
                    $new_y1 = min(max($pos1->getY() - $args["blocks"], World::Y_MIN), World::Y_MAX);
                    $new_y2 = max(min($pos2->getY() + $args["blocks"], World::Y_MAX), World::Y_MIN);
                } else { // Expand all the way up and down
                    $new_y1 = World::Y_MIN;
                    $new_y2 = World::Y_MAX;
                }
                SelectionManager::selectFirstPosition($sender,
                            new Position($pos1->getX(), $new_y1, $pos1->getZ(), $world));
                SelectionManager::selectSecondPosition($sender,
                            new Position($pos2->getX(), $new_y2, $pos2->getZ(), $world));
                $exp_diff1 = $pos1->getY() - $new_y1;
                $exp_diff2 = $pos2->getY() - $new_y2;
                $sender->sendMessage($this->lang_manager->translate("command.expand.success.both", [$exp_diff1, $exp_diff2]));
                break;
            case null:
            default:
                $sender->sendMessage($this->lang_manager->translate("command.expand.usage"));
        }
    }
} 
 