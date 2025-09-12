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

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\RawStringArgument;

use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\utils\LandManager;

class UnsetspawnSubcommand extends BaseSubCommand {

    private LangManager $lang_manager;
    private LandManager $land_manager;

    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("land name", true));
        
        $this->setPermission("cerberus.command.unsetspawn");
        
        $this->lang_manager = LangManager::getInstance();
        $this->land_manager = LandManager::getInstance();
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        if (!isset($args["land name"])) {
            $sender->sendMessage($this->lang_manager->translate("command.unsetspawn.should_specify_land_name"));
            return;
        }
        $land = $this->land_manager->getLandByName($args["land name"]);
        if (!isset($land)) {
            $sender->sendMessage($this->lang_manager->translate("command.land_does_not_exist", [$args["land name"]]));
            return;
        }
        if (!$land->isOwner($sender) && !$sender->hasPermission("cerberus.command.unsetspawn.other")) {
            $sender->sendMessage($this->lang_manager->translate("command.unsetspawn.no_other"));
            return;
        }
        if ($land->getSpawnpoint() === null) {
            $sender->sendMessage($this->lang_manager->translate("command.unsetspawn.land_has_no_spawnpoint"));
            return;
        }
        $land->unsetSpawnpoint();
        if (!$land->isOwner($sender)) {
            $sender->sendMessage($this->lang_manager->translate("command.unsetspawn.success.other", [$land->getName(), implode(", ", $land->getOwnerNames())]));
        } else {
            $sender->sendMessage($this->lang_manager->translate("command.unsetspawn.success", [$land->getName()]));
        }
    }
}
