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

/**
 * Requires more work to be done. Coming soon
 */
class WhitelistSubcommand extends BaseSubCommand {
    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("operation"));
        $this->registerArgument(1, new RawStringArgument("land_name"));
        $this->registerArgument(2, new RawStringArgument("player"));
        
        $this->setPermission("cerberus.command.whitelist");

        $this->api = CerberusAPI::getInstance();
        $this->config_manager = ConfigManager::getInstance();
        $this->lang_manager = LangManager::getInstance();
    }

    public function onRun(CommandSender $sender, string $alias, array $args): void {
        if (!isset($args[0])) {
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.specify"));
            return;
        }

        switch ($args[0]) {
            case "add":
                if (!isset($args[1])) {
                    $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.should_specify_land_name"));
                    return;
                }
                if (!isset($args[2])) {
                    $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.specify.player"));
                    return;
                }

                $land = $this->api->getLandByName($args[1]);
                if ($land === null) {
                    $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.land_does_not_exist", [$args[1]]));
                    return;
                }
                if ($land->getOwner() !== $sender->getName() && !$sender->hasPermission("cerberus.command.whitelist.add")) {
                    $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.no_other"));
                    return;
                }

                $this->api->addPlayerToWhitelist($args[2]);
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.success", [$args[1]]));
                break;

            case "remove":
                if (!isset($args[1])) {
                    $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.should_specify_land_name.other"));
                    return;
                }
                if (!isset($args[2])) {
                    $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.specify.player"));
                    return;
                }

                $land = $this->api->getLandByName($args[1]);
                if ($land === null) {
                    $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.land_does_not_exist", [$args[1]]));
                    return;
                }
                if ($land->getOwner() !== $sender->getName() && !$sender->hasPermission("cerberus.command.whitelist.remove")) {
                    $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.no_other"));
                    return;
                }

                $this->api->removePlayerFromWhitelist($args[2]);
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.success.other", [$args[1]]));
                break;

            default:
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.whitelist.invalidargs"));
                break;
        }
    }
}
