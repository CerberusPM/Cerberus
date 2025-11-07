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
use pocketmine\player\Player;
use pocketmine\world\Position;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\Vector3Argument;

use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\utils\LandManager;

class SetspawnSubcommand extends CerberusSubcommand {

    private LangManager $lang_manager;
    private LandManager $land_manager;

    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("land name", true));
        $this->registerArgument(1, new Vector3Argument("position", true)); //Optional. Uses player current position if not set.
        
        $this->setPermission("cerberus.command.setspawn");
        
        $this->lang_manager = LangManager::getInstance();
        $this->land_manager = LandManager::getInstance();
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        if (!isset($args["land name"])) {
            $sender->sendMessage($this->lang_manager->translate("command.setspawn.should_specify_land_name"));
            return;
        }
        $land = $this->land_manager->getLandByName($args["land name"]);
        if (!isset($land)) {
            $sender->sendMessage($this->lang_manager->translate("command.land_does_not_exist", [$args["land name"]]));
            return;
        }
        if (!$land->isOwner($sender) && !$sender->hasPermission("cerberus.command.setspawn.other")) {
            $sender->sendMessage($this->lang_manager->translate("command.setspawn.no_other"));
            return;
        }
        if (!isset($args["position"])) {
            if (!$sender instanceof Player) {
                $sender->sendMessage($this->lang_manager->translate("command.setspawn.should_specify_position"));
                return;
            }
            $new_pos = $sender->getPosition();
        } else {
            $new_pos = Position::fromObject($args["position"], $this->getOwningPlugin()->getServer()->getWorldManager()->getWorldByName($land->getWorldName()));
        } //We assume that user means position from the world where the landclaim is
        if (!$land->containsPosition($new_pos)) { //Check if specified position is in landclaim bounds
            $sender->sendMessage($this->lang_manager->translate("command.setspawn.position_not_in_land"));
            return;
        }
        $land->setSpawnpoint($new_pos);
        $sender->sendMessage($this->lang_manager->translate("command.setspawn.success", [$args["land name"], $new_pos->getX(), $new_pos->getY(), $new_pos->getZ()]));
    }
} 
