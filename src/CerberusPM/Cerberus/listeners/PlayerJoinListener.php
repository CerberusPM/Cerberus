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

namespace CerberusPM\Cerberus\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;

use CerberusPM\Cerberus\Cerberus;
use CerberusPM\Cerberus\utils\PlayerCacheManager;

/**
 * An event listener currently used by PlayerCacheManager, which automatically adds players to cache
 */
class PlayerJoinListener implements Listener {
    private PlayerCacheManager $cache_manager;
    
    function __construct(Cerberus $plugin) {
        $this->cache_manager = $plugin->getPlayerCache();
    }
    
    /**
     * Add player's name and uuid to the database if their name is not found or doesn't match the previous one
     * 
     * @param PlayerJoinEvent $event
     */
    public function onPlayerLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        if ($this->cache_manager->getNameByUuid($player->getUniqueID()) !== $player->getName()) {
            $this->cache_manager->setUuidByName($player->getName(), $player->getUniqueID());
        }
    }
    
}
