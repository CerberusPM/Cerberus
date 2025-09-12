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
use pocketmine\player\OfflinePlayer;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\RawStringArgument;

use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\utils\LandManager;

class RemoveOwnerSubcommand extends BaseSubCommand {
    
    private LangManager $lang_manager;
    private LandManager $land_manager;

    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("land name", true));
        $this->registerArgument(1, new RawStringArgument("player name", true));
        
        $this->setPermission("cerberus.command.removeowner");
        
        $this->lang_manager = LangManager::getInstance();
        $this->land_manager = LandManager::getInstance();
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        // Check if args are set
        if (!isset($args["land name"])) {
            $sender->sendMessage($this->lang_manager->translate("command.should_specify_land_name"));
            return;
        }
        if (!isset($args["player name"])) {
            $sender->sendMessage($this->lang_manager->translate("command.should_specify_player"));
            return;
        }
        // Get the land
        $land = $this->land_manager->getLandByName($args["land name"]);
        if (is_null($land)) {
            $sender->sendMessage($this->lang_manager->translate("command.land_does_not_exist", [$args["land name"]]));
            return;
        }
        // Check ownership
        if (!$land->isOwner($sender) && !$sender->hasPermission("cerberus.command.removeowner.other")) {
            $sender->sendMessage($this->lang_manager->translate("command.removeowner.no_other"));
            return;
        }
        // Get the player
        $player = $this->getOwningPlugin()->getServer()->getPlayerByPrefix($args["player name"]);
        if (!isset($player)) {
            $player = getOwningPlugin()->getServer()->getOfflinePlayer($args["player name"]);
        }
        if (!isset($player) || ($player instanceof OfflinePlayer && !$player->hasPlayedBefore())) {
                $sender->sendMessage($this->lang_manager->translate("command.player_not_found", [$args["player name"]]));
                return;
        }
        // Check if player is land owner
        if (!$land->isOwner($player)) {
             $sender->sendMessage($this->lang_manager->translate("command.removeowner.not_an_owner", [$player->getDisplayName(), $land->getName()]));
             return;
        }
        // Finally remove the player from the owner list
        $land->removeOwner($player);
        $sender->sendMessage($this->lang_manager->translate("command.removeowner.success", [$player->getDisplayName()]));
    }
} 
