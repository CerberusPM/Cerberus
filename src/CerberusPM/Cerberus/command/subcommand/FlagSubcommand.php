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
use CortexPE\Commando\args\BooleanArgument;

use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\utils\LandManager;
use CerberusPM\Cerberus\utils\FlagManager;

class FlagSubcommand extends BaseSubCommand {
    
    private LangManager $lang_manager;
    private LandManager $land_manager;
    private FlagManager $flag_manager;
    
    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("land name", true)); //Landclaim name
        $this->registerArgument(1, new RawStringArgument("flag name", true));
        $this->registerArgument(2, new BooleanArgument("enabled", true)); //True/False whether flag should be enabled or not
        
        $this->setPermission("cerberus.command.flag");
        
        $this->lang_manager = LangManager::getInstance();
        $this->land_manager = LandManager::getInstance();
        $this->flag_manager = FlagManager::getInstance();
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
         // Check if args are set
        if (!isset($args["land name"])) {
            $sender->sendMessage($this->lang_manager->translate("command.should_specify_land_name"));
            return;
        }
        if (!isset($args["flag name"])) {
            // Create a pretty flag list with translated descriptions
            $available_flags = "\n";
            foreach ($this->flag_manager->getFlags() as $flag) {
                if ($flag->isRegistered()) {
                    $available_flags .= "&e• " . $flag->getName() . " - " . $flag->getDescription() . "\n";
                }
            }
            $sender->sendMessage($this->lang_manager->translate("command.flag.should_specify_flag", [$available_flags]));
            return;
        }
        if (!isset($args["enabled"])) {
            $sender->sendMessage($this->lang_manager->translate("command.flag.should_specify_enabled"));
            return;
        }
        // Get the land
        $land = $this->land_manager->getLandByName($args["land name"]);
        if (is_null($land)) {
            $sender->sendMessage($this->lang_manager->translate("command.land_does_not_exist", [$args["land name"]]));
            return;
        }
        // Check ownership
        if (!$land->isOwner($sender) && !$sender->hasPermission("cerberus.command.flag.other")) {
            $sender->sendMessage($this->lang_manager->translate("command.flag.no_other"));
            return;
        }
        // Check flag validity
        $flag = $this->flag_manager->getFlagByName($args["flag name"]);
        if (!isset($flag)) {
            $sender->sendMessage($this->lang_manager->translate("command.flag.flag_not_found", [$args["flag name"]]));
            return;
        }
        // Check if sender has permission to use the flag
        if (!$sender->hasPermission($flag->getPermission())) {
            $sender->sendMessage($this->lang_manager->translate("command.flag.no_permission_to_flag", [$flag->getName()]));
            return;
        }
        // Check if plugin is registered (turned on)
        if (!$flag->isRegistered()) {
            $sender->sendMessage($this->lang_manager->translate("command.flag.flag_is_off", [$flag->getName()]));
            return;
        }
        // Enable the flag
        if ($args["enabled"]) {
            if ($land->hasFlag($flag)) {
                $sender->sendMessage($this->lang_manager->translate("command.flag.already_enabled", [$flag->getName()]));
                return;
            }
            $land->addFlag($flag);
            $sender->sendMessage($this->lang_manager->translate("command.flag.success_enable", [$flag->getName(), $land->getName()]));
        } else {
            if (!$land->hasFlag($flag)) {
                $sender->sendMessage($this->lang_manager->translate("command.flag.already_disabled", [$flag->getName(), $land->getName()]));
                return;
            }
            $land->removeFlag($flag);
            $sender->sendMessage($this->lang_manager->translate("command.flag.success_disable", [$flag->getName(), $land->getName()]));
        }
    }
} 
 
