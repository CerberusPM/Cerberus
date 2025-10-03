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

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\RawStringArgument;

use CerberusPM\Cerberus\utils\ConfigManager;
use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\utils\LandManager;

use function count;
use function is_null;
use function implode;
use function rtrim;

class InfoSubcommand extends BaseSubCommand {

    private ConfigManager $config_manager;
    private LangManager $lang_manager;
    private LandManager $land_manager;

    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("land name", true));
        
        $this->setPermission("cerberus.command.info");
        
        $this->config_manager = ConfigManager::getInstance();
        $this->lang_manager = LangManager::getInstance();
        $this->land_manager = LandManager::getInstance();
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        if (count($args) < 1) {
            if ($sender instanceof Player) {
                $sender->sendMessage($this->lang_manager->translate("command.info.should_specify_land_name.player"));
            } else {
                $sender->sendMessage($this->lang_manager->translate("comamnd.info.should_specify_land_name.console"));
            }
            return;
        }
        $land = $this->land_manager->getLandByName($args["land name"]);
        if (!isset($land)) {//Landclaim not found
            $sender->sendMessage($this->lang_manager->translate("command.land_does_not_exist", [$args["land name"]]));
            return;
        }
        $creation_date = $land->getFormattedCreationDate();
        if (empty($creation_date)) { //That may happen if format is empty or improperly set
            $creation_date = $this->lang_manager->translate("command.info.no_info", include_prefix: false);
        }
        if (is_null($land->getSpawnpoint())) {
            $spawn_x = $spawn_y = $spawn_z = $this->lang_manager->translate("command.info.not_set", include_prefix: false);
        } else {
            $spawn_x = $land->getSpawnpoint()->getX();
            $spawn_y = $land->getSpawnpoint()->getY();
            $spawn_z = $land->getSpawnpoint()->getZ();
        }
        // Compose colored flag list string
        $flag_list = "";
        foreach ($land->getFlags() as $flag) {
            if ($flag->isRegistered()) {
                $flag_list .= "&e" . $flag->getName() . ", ";
            } else {
                $flag_list .= "&7" . $flag->getName() . ", ";
            }
        }
        $message = $this->lang_manager->translate("command.info.info", [
            $land->getName(), $land->getCreatorName(), implode(", ", $land->getOwnerNames()), implode(", ", $land->getMemberNames()), $land->getWorldName(), $creation_date,
            $land->getFirstPosition()->getX(), $land->getFirstPosition()->getY(), $land->getFirstPosition()->getZ(),
            $land->getSecondPosition()->getX(), $land->getSecondPosition()->getY(), $land->getSecondPosition()->getZ(),
            $spawn_x, $spawn_y, $spawn_z, $land->getLength(), $land->getWidth(), $land->getHeight(), rtrim($flag_list, ", "), $land->getArea(), $land->getVolume()], false);
        foreach ($message as $string) {
            $sender->sendMessage($this->config_manager->getPrefix() . $string);
        }
    }
} 
