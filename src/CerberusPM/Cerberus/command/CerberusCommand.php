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
    
    protected function prepare(): void {
        $subcommands = [
            new ClaimSubcommand("claim", "Claim land", ["create", "new", "c"]),
            new ExpandSubcommand("expand", "Expand your selection", ["exp", "e"]),
            new FirstPositionSubcommand("pos1", "Select first position", ["1", "first"]),
            new FlagSubcommand("flag", "Manage land flags", ["f"]),
            new HelpSubcommand("help", "Get usage information", ["h", "?", "how"]),
            new HereSubcommand("here", "Get name of the land you are in", ["aqui"]),
            new ListSubcommand("list", "List landclaims", ["l"]),
            new InfoSubcommand("info", "Get detailed information about a land", ["i", "information"]),
            new MoveSubcommand("move", "Move a landclaim", ["mv", "mov", "m"]),
            new ReloadSubcommand("reload", "Reload plugin config and/or language", ["rel","rld"]),
            new RemoveSubcommand("remove", "Remove a landclaim", ["rm", "rem", "rmv", "delete", "erase", "r", "d"]),
            new SecondPositionSubcommand("pos2", "Select second position", ["2", "second"]),
            new SetspawnSubcommand("setspawn", "Set teleportation point for a landclaim", ["s", "spawn", "set"]),
            new TeleportSubcommand("teleport", "Teleport to land's spawnpoint", ["tp", "to", "tpto"]),
            new UnsetspawnSubcommand("unsetspawn", "Remove landclaim's spawnpoint", ["us", "unset", "rmspawn", "delspawn", 'clearspawn']),
            new WandSubcommand("wand", "Get a selection wand", ["wnd", "w", "thingy"]),
            new AddMemberSubcommand("addmember", "Add a player to member list", ["am", "whitelist", "addmbr", "add"]),
            new AddOwnerSubcommand("addowner", "Add a player to owner list", ["ao", "addown"]),
            new RemoveMemberSubcommand("removemember", "Remove a player from member list", ["remmember", "delmember", "delmember", "rmmember", "rmmbr"]),
            new RemoveOwnerSubcommand("removeowner", "Remove a player from owner list", ["remowner", "rmowner", "rmowr", "delowner", "delowr"])
        ];
        
        foreach($subcommands as $subcmd) {
            $this->registerSubCommand($subcmd);
        }
        
        $this->setPermission(self::BASE_PERMISSION);
    }
    
    public function getPermission(): string {
        return self::BASE_PERMSISSION;
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        $sender->sendMessage($this->getOwningPlugin()->getLangManager()->translate("plugin.in-dev"));
    }
    
}
