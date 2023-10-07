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
use Levonzie\Cerberus\utils\SelectionManager;
use Levonzie\Cerberus\utils\ConfigManager;
use Levonzie\Cerberus\utils\LangManager;
use Levonzie\Cerberus\utils\LandManager;
use Levonzie\Cerberus\Landclaim;

use function is_null;

class ClaimSubcommand extends BaseSubCommand {
    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("name")); //Name of a landclaim
        
        $this->setPermission("cerberus.command.claim");
        
        $this->api = CerberusAPI::getInstance();
        $this->config_manager = ConfigManager::getInstance();
        $this->lang_manager = LangManager::getInstance();
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        $selector = $sender->getName();
        //Check if all positions are selected 
        if (!SelectionManager::hasSelectedFirst($selector) && !SelectionManager::hasSelectedSecond($selector)) {
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.select_both_positions"));
            return;
        }
        elseif (!SelectionManager::hasSelectedFirst($selector)) {
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.select_pos1"));
            return;
        }
        elseif (!SelectionManager::hasSelectedSecond($selector)) {
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.select_pos2"));
            return;
        }
        else {
            $pos1 = SelectionManager::getSelectedFirstPosition($selector);
            $pos2 = SelectionManager::getSelectedSecondPosition($selector);
        }
        //Check if positions are located in the same world
        if ($pos1[1] != $pos2[1]) {
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.world_mismatch"));
            return;
        } else {
            $world = $pos1[1];
        }
        //TODO: claim limit permission
        //Check if land already exists
        if ($this->api->landExists($args["name"])) {
            $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.already_exists", [$args["name"]]));
            return;
        }
        $new_land = new Landclaim($args["name"], $selector, $pos1[0], $pos2[0], $world);
        //Check if intersects land owned by somebody else
        $intersecting_land = $this->api->getIntersectingLand($new_land);
        if (!is_null($intersecting_land) && $intersecting_land->getOwner() != $selector) {
            if (!$sender->hasPermission("cerberus.command.claim.bypass_intersect")) {
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.intersects", [$intersecting_land->getName(), $intersecting_land->getOwner()]));
                return;
            } else
                $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.intersects_notification", [$intersecting_land->getName(), $intersecting_land->getOwner()]));
        }
        //Finally create a landclaim
        LandManager::registerLandclaim($new_land);
        SelectionManager::deselectAll($selector);
        
        $sender->sendMessage($this->config_manager->getPrefix() . $this->lang_manager->translate("command.claim.success", [$args["name"]]));
    }
}
