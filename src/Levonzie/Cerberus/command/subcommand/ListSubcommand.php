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
use Levonzie\Cerberus\utils\ConfigManager;
use Levonzie\Cerberus\utils\LangManager;

use function count;

class ListSubcommand extends BaseSubCommand {
    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("player name", true));
        
        $this->setPermission("cerberus.command.list");
        
        $this->api = CerberusAPI::getInstance();
        $this->config_manager = ConfigManager::getInstance();
        $this->lang_manager = LangManager::getInstance();
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        if (count($args) == 0) { //Checking landclaims of sender
            $landclaims = $this->api->listLandOwnedBy($sender->getName());
            if (empty($landclaims)) {
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.list.none"));
                return;
            }
            if (count($landclaims) == 1) {
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.list.one_land", [$landclaims[0]->getName()]));
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.info.advertisement.specific", [$landclaims[0]->getName()]));
                return;
            }
            $landclaim_list_message = "";
            foreach($landclaims as $index => $land) {
                if ($index < count($landclaims) - 1) // Add a comma at the end if not the last
                    $landclaim_list_message .= $land->getName() . ', ';
                else
                    $landclaim_list_message .= $land->getName();
            }
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.list.land_list", [count($landclaims), $landclaim_list_message]));
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.info.advertisement.general"));
        } else { //Checking landclaims of another owner
            if (!$sender->hasPermission("cerberus.command.list.other")) {
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.list.no_other"));
                return;
            }
            $landclaims = $this->api->listLandOwnedBy($args["player name"]);
            if (empty($landclaims)) {
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.list.other.none", [$args["player name"]]));
                return;
            }
            if (count($landclaims) == 1) {
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.list.other.one_land", [$args["player name"], $landclaims[0]->getName()]));
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.info.advertisement.specific", [$landclaims[0]->getName()]));
                return;
            }
            $landclaim_list_message = "";
            foreach($landclaims as $index => $land) {
                if ($index < count($landclaims) - 1) // Add a comma at the end if not the last
                    $landclaim_list_message .= $land->getName() . ', ';
                else
                    $landclaim_list_message .= $land->getName();
            }
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.list.other", [$args["player name"], count($landclaims), $landclaim_list_message]));
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.info.advertisement.general"));
        }
    }
} 
