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

namespace CerberusPM\Cerberus\flags;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;

class NoInteractFlag extends Flag implements Listener {
    
    function __construct() {
        $this->name = "NoInteract";
        $this->name_aliases = ["nouse", "no_use", "interact", "notouch", "no_touch",
            "no_using", "nousing", "no_interacting", "nointeracting", "use", "touch", "interact"];
        $this->id = "no_interact";
        $this->description = "flag.no_interact.description";
        $this->permission = "cerberus.flag.no_interact";
    }
    
    public function onInteract(PlayerInteractEvent $event): void  {
        if (!$this->is_registered) {
            return;
        }
        $player = $event->getPlayer();
        $position = $event->getBlock()->getPosition();

        foreach ($this->landclaims as $land) {
            if ($land->containsPosition($position) && !$land->isOwner($player) 
                    && !$land->isMember($player) && !$player->hasPermission($this->permission . ".bypass")) {
                $event->cancel(true);
            }
        }
    }
    
}
