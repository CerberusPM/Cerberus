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

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;

class NoPVPFlag extends Flag implements Listener {
    
    function __construct() {
        $this->name = "NoPVP";
        $this->name_aliases = ["pvp", "nofight", "fight", "stoppvp", "stop_pvp", "no_fight", "antipvp", "anti_pvp"];
        $this->id = "no_pvp";
        $this->description = "flag.no_pvp.description";
        $this->permission = "cerberus.flag.no_pvp";
    }
    
    public function onPVP(EntityDamageByEntityEvent $event): void  {
        if (!$this->is_registered) {
            return;
        }
        
        if ($event->getEntity() instanceof Player && $event->getDamager() instanceof Player) {
            $player = $event->getEntity();
            $damager = $event->getDamager();
            $position = $player->getPosition();

            foreach ($this->landclaims as $land) {
                if ($land->containsPosition($position) && !$damager->hasPermission($this->permission . ".bypass")) {
                    $event->cancel(true);
                }
            }
        }
    }
    
}
