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

namespace Levonzie\Cerberus\command;

use pocketmine\command\CommandSender;

use CortexPE\Commando\BaseCommand;

use Levonzie\Cerberus\command\subcommand\ClaimSubcommand;
use Levonzie\Cerberus\command\subcommand\ExpandSubcommand;
use Levonzie\Cerberus\command\subcommand\FirstPositionSubcommand;
use Levonzie\Cerberus\command\subcommand\FlagSubcommand;
use Levonzie\Cerberus\command\subcommand\HelpSubcommand;
use Levonzie\Cerberus\command\subcommand\HereSubcommand;
use Levonzie\Cerberus\command\subcommand\InfoSubcommand;
use Levonzie\Cerberus\command\subcommand\ListSubcommand;
use Levonzie\Cerberus\command\subcommand\MoveSubcommand;
use Levonzie\Cerberus\command\subcommand\ReloadSubcommand;
use Levonzie\Cerberus\command\subcommand\RemoveSubcommand;
use Levonzie\Cerberus\command\subcommand\SecondPositionSubcommand;
use Levonzie\Cerberus\command\subcommand\SetspawnSubcommand;
use Levonzie\Cerberus\command\subcommand\TeleportSubcommand;
use Levonzie\Cerberus\command\subcommand\WandSubcommand;
use Levonzie\Cerberus\command\subcommand\WhitelistSubcommand;


class CerberusCommand extends BaseCommand {
    private const BASE_PERMISSION = "cerberus.command";
    
    protected function prepare(): void {
        $this->registerSubCommand(new ClaimSubcommand("claim", "Claim land", ["create", "new", "c"]));
        $this->registerSubCommand(new ExpandSubcommand("expand", "Expand your selection", ["exp", "e"]));
        $this->registerSubCommand(new FirstPositionSubcommand("pos1", "Select first position", ["1", "first"]));
        $this->registerSubCommand(new FlagSubcommand("flag", "Manage land flags", ["f"]));
        $this->registerSubCommand(new HelpSubcommand("help", "Get usage information", ["h", "?", "how"]));
        $this->registerSubCommand(new HereSubcommand("here", "Get name of the land you are in", ["aqui"]));
        $this->registerSubCommand(new ListSubcommand("list", "List landclaims", ["l"]));
        $this->registerSubCommand(new InfoSubcommand("info", "Get detailed information about a land", ["i", "information"]));
        $this->registerSubCommand(new MoveSubcommand("move", "Move a landclaim", ["mv", "mov", "m"]));
        $this->registerSubCommand(new ReloadSubcommand("reload", "Reload plugin config and/or language", ["rel","rld"]));
        $this->registerSubCommand(new RemoveSubcommand("remove", "Remove a landclaim", ["rm", "rem", "rmv", "delete", "erase", "r", "d"]));
        $this->registerSubCommand(new SecondPositionSubcommand("pos2", "Select second position", ["2", "second"]));
        $this->registerSubCommand(new SetspawnSubcommand("setspawn", "Set teleportation and movement point", ["s", "spawn", "set"]));
        $this->registerSubCommand(new TeleportSubcommand("teleport", "Teleport to land's spawnpoint", ["tp", "to", "tpto"]));
        $this->registerSubCommand(new WandSubcommand("wand", "Get a selection wand", ["wnd", "w", "thingy"]));
        $this->registerSubCommand(new WhitelistSubcommand("whitelist", "Manage who can access your land", ["white", "invite"]));
        
        
        $this->setPermission(self::BASE_PERMISSION);
    }
    
    public function getPermission(): string {
        return self::BASE_PERMSISSION;
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        $sender->sendMessage($this->getOwningPlugin()->getLangManager()->translate("plugin.in-dev"));
    }
    
}
