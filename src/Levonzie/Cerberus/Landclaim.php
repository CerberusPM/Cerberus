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

namespace Levonzie\Cerberus;

use pocketmine\world\Position;
use pocketmine\math\Vector3;

class Landclaim {
    protected string $name;
    protected string $owner;
    protected Vector3 $pos1;
    protected Vector3 $pos2;
    protected string $world_name;
    
    public function __construct(string $name, string $owner, Vector3 $pos1, Vector3 $pos2, string $world_name) {
        $this->name = $name;
        $this->owner = $owner;
        //Optimize positions for containsPosition() calculation speed boost
        $this->pos1 = Vector3::minComponents($pos1, $pos2);
        $this->pos2 = Vector3::maxComponents($pos1, $pos2);
        $this->world_name = $world_name;
    }
    
    /**
     * @return string Landclaim name
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * @return string Lanclaim owner
     */
    public function getOwner(): string {
        return $this->owner;
    }
    
    /**
     * @return Vector3 Landclaim's first position
     */
    public function getFirstPosition(): Vector3 {
        return $this->pos1;
    }
    
    /**
     * @return Vector3 Landclaim's second position
     */
    public function getSecondPosition(): Vector3 {
        return $this->pos2;
    }
    
    /**
     * @return string Name of the world where landclaim is located
     */
    public function getWorldName(): string {
        return $this->world_name;
    }
    
    /**
     * Check whether landclaim contains a position
     * 
     * @param Position $pos Position to be checked for inclusion in landclaim
     * 
     * @return bool Whether landclaim contains given position or not
     */
    public function containsPosition(Position $pos): bool {
        if ($pos->getWorld()->getFolderName() === $this->getWorldName() &&
                $pos->getX() >= $this->getFirstPosition()->getX() &&
                $pos->getFloorX() <= $this->getSecondPosition()->getX() &&
                $pos->getY() >= $this->getFirstPosition()->getY() &&
                $pos->getFloorY() <= $this->getSecondPosition()->getY() &&
                $pos->getZ() >= $this->getFirstPosition()->getZ() &&
                $pos->getFloorZ() <= $this->getSecondPosition()->getZ())
            return true;
        return false;
    }
    
    /**
     * Check whether landclaim intersects another landclaim
     * 
     * @param Landclaim $target A landclaim to check for intersection with this landclaim
     * 
     * @return bool True if landclaims intersect, false if not
     */
    public function intersectsLandclaim(Landclaim $target): bool {
        if ($target->getWorldName() === $this->getWorldName() &&
                $target->getFirstPosition()->getX() <= $this->getSecondPosition()->getX() &&
                $target->getSecondPosition()->getX() >= $this->getFirstPosition()->getX() &&
                $target->getFirstPosition()->getY() <= $this->getSecondPosition()->getY() &&
                $target->getSecondPosition()->getY() >= $this->getFirstPosition()->getY() &&
                $target->getFirstPosition()->getZ() <= $this->getSecondPosition()->getZ() &&
                $target->getSecondPosition()->getZ() >= $this->getFirstPosition()->getZ())
            return true;
        return false;
    }
}
