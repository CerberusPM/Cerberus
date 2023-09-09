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
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use pocketmine\inventory\{PlayerInventory, Inventory};
use pocketmine\item\VanillaItems;

use CortexPE\Commando\BaseSubCommand;

use Levonzie\Cerberus\utils\ConfigManager;
use Levonzie\Cerberus\utils\LangManager;

class WandSubcommand extends BaseSubCommand {
    protected function prepare(): void {
        $this->setPermission("cerberus.command.wand");
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        $lang_manager = LangManager::getInstance();
        if (!$sender instanceof Player) {
            $sender->sendMessage($lang_manager->translate("command.in-game"));
        }
        else {
            $sender->sendMessage(ConfigManager::getInstance()->getPrefix() . $lang_manager->translate("command.wand.given"));  
            $sender->getInventory()->addItem(VanillaItems::STONE_AXE()->setCustomName("§r§l§gCerberus Wand§r"));
        }
    }
} 
 
