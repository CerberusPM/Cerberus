<?php

/**
 * Cerberus - an advanced land protection plugin for PocketMine-MP 5.
 * Copyright (C) 2023 skyss0fly and Levonzie
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
use CortexPE\Commando\BaseSubCommand
class WandSubcommand extends BaseSubCommand {
    protected function prepare(): void {
        $this->setPermission("cerberus.command.wand");
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        if (!$sender instanceof Player){
$sender->sendMessage(TEXTFORMAT::RED . TEXTFORMAT::BOLD . "Error: must be in-game.")
        }
        elseif (!$sender->hasPermission("cerberus.command.wand")){
            $sender->sendMessage(TEXTFORMAT::RED . TEXTFORMAT::BOLD . "You don't have permission to use this command.")
        }
        else {
            $sender->sendMessage(TEXTFORMAT::BOLD . TEXTFORMAT::GREEN . "+" . TEXTFORMAT::YELLOW . "-" . TEXTFORMAT::GOLD . "Cerberus" .  TEXTFORMAT::YELLOW . "-" . TEXTFORMAT::GREEN . "+" . TEXFORMAT::BLUE . " Gave you a Wand");  
            $sender->getInventory()->addItem(VanillaItems::STONE_AXE)->setCustomName("§r§l§gCerberus Wand§r");
        }
    }
} 
 
