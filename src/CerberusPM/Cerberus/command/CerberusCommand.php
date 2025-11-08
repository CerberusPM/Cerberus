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

namespace CerberusPM\Cerberus\command;

use pocketmine\command\CommandSender;

use CortexPE\Commando\BaseCommand;

use CerberusPM\Cerberus\command\subcommand\ClaimSubcommand;
use CerberusPM\Cerberus\command\subcommand\ExpandSubcommand;
use CerberusPM\Cerberus\command\subcommand\FirstPositionSubcommand;
use CerberusPM\Cerberus\command\subcommand\FlagSubcommand;
use CerberusPM\Cerberus\command\subcommand\HelpSubcommand;
use CerberusPM\Cerberus\command\subcommand\HereSubcommand;
use CerberusPM\Cerberus\command\subcommand\InfoSubcommand;
use CerberusPM\Cerberus\command\subcommand\ListSubcommand;
use CerberusPM\Cerberus\command\subcommand\MoveSubcommand;
use CerberusPM\Cerberus\command\subcommand\ReloadSubcommand;
use CerberusPM\Cerberus\command\subcommand\RemoveSubcommand;
use CerberusPM\Cerberus\command\subcommand\SecondPositionSubcommand;
use CerberusPM\Cerberus\command\subcommand\SetspawnSubcommand;
use CerberusPM\Cerberus\command\subcommand\TeleportSubcommand;
use CerberusPM\Cerberus\command\subcommand\UnsetspawnSubcommand;
use CerberusPM\Cerberus\command\subcommand\WandSubcommand;
use CerberusPM\Cerberus\command\subcommand\AddMemberSubcommand;
use CerberusPM\Cerberus\command\subcommand\AddOwnerSubcommand;
use CerberusPM\Cerberus\command\subcommand\RemoveMemberSubcommand;
use CerberusPM\Cerberus\command\subcommand\RemoveOwnerSubcommand;


class CerberusCommand extends BaseCommand {
    private const BASE_PERMISSION = "cerberus.command";
    private array $subcommands = [];


    protected function prepare(): void {
        $this->subcommands = [
            new ClaimSubcommand("claim", "command.description.claim", ["create", "new", "c"]),
            new ExpandSubcommand("expand", "command.description.expand", ["exp", "e"]),
            new FirstPositionSubcommand("pos1", "command.description.pos1", ["1", "first"]),
            new SecondPositionSubcommand("pos2", "command.description.pos2", ["2", "second"]),
            new FlagSubcommand("flag", "command.description.flag", ["f"]),
            new HelpSubcommand("help", "command.description.help", ["h", "?", "how"]),
            new HereSubcommand("here", "command.description.here", ["aqui"]),
            new ListSubcommand("list", "command.description.list", ["l"]),
            new InfoSubcommand("info", "command.description.info", ["i", "information"]),
            new MoveSubcommand("move", "command.description.move", ["mv", "mov", "m"]),
            new ReloadSubcommand("reload", "command.description.reload", ["rel","rld"]),
            new RemoveSubcommand("remove", "command.description.remove", ["rm", "rem", "rmv", "delete", "erase", "r", "d"]),
            new SetspawnSubcommand("setspawn", "command.description.setspawn", ["s", "spawn", "set"]),
            new TeleportSubcommand("teleport", "command.description.teleport", ["tp", "to", "tpto"]),
            new UnsetspawnSubcommand("unsetspawn", "command.description.unsetspawn", ["us", "unset", "rmspawn", "delspawn", 'clearspawn']),
            new WandSubcommand("wand", "command.description.wand", ["wnd", "w", "thingy"]),
            new AddMemberSubcommand("addmember", "command.description.addmember", ["am", "whitelist", "addmbr", "add"]),
            new AddOwnerSubcommand("addowner", "command.description.addowner", ["ao", "addown"]),
            new RemoveMemberSubcommand("removemember", "command.description.removemember", ["remmember", "delmember", "delmember", "rmmember", "rmmbr"]),
            new RemoveOwnerSubcommand("removeowner", "command.description.removeowner", ["remowner", "rmowner", "rmowr", "delowner", "delowr"])
        ];
        
        foreach($this->subcommands as $subcmd) {
            $this->registerSubCommand($subcmd);
        }
        
        $this->setPermission(self::BASE_PERMISSION);
    }
    
    public function getSubcommands(): array {
        return $this->subcommands;
    }
    
    public function getPermission(): string {
        return self::BASE_PERMSISSION;
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        $lang_manager = $this->getOwningPlugin()->getLangManager();
       
        $sender->sendMessage($lang_manager->translate("plugin.in-dev"));
        $sender->sendMessage($lang_manager->translate("plugin.do_help", include_prefix: false));
        $sender->sendMessage($lang_manager->translate("plugin.version", [$this->getOwningPlugin()->getDescription()->getVersion()], false));
        $sender->sendMessage($lang_manager->translate("plugin.selected_language", include_prefix: false));
        $sender->sendMessage($lang_manager->translate("plugin.authors", include_prefix: false));
    }
    
}
