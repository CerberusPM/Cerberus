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

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;

class InvincibleFlag extends Flag implements Listener {
    
    function __construct() {
        $this->name = "Invincible";
        $this->name_aliases = ["god", "invincibility", "nodamage", "no_damage"];
        $this->id = "invincible";
        $this->description = "flag.invincible.description";
        $this->permission = "cerberus.flag.invincible";
    }
    
    public function onDamage(EntityDamageEvent $event): void  {
        if (!$this->is_registered) {
            return;
        }
        if ($event->getEntity() instanceof Player) {
            $player = $event->getEntity();
            $position = $player->getPosition();

            foreach ($this->landclaims as $land) {
                if ($land->containsPosition($position)) { // There's no point in bypassing this flag
                    $event->cancel(true);
                }
            }
        }
    }
    
}
