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

namespace CerberusPM\Cerberus\command\subcommand;

use CortexPE\Commando\BaseSubCommand;
use CerberusPM\Cerberus\utils\LangManager;

/**
 * Extension class for Commando's BaseSubcommand to implement dynamic usage message translation
 */
abstract class CerberusSubcommand extends BaseSubCommand {
    
    private string $description_key;
    
    public function __construct(string $name, string $description = "", array $aliases = []) {
        $this->description_key = $description;
        if (!empty($description)) {
            $description = LangManager::getInstance()->translateDefault($description);
        }
        parent::__construct($name, $description, $aliases);
    }
    
    public function getTranslatedDescription(): string {
        if (!empty($this->description_key)) {
            return LangManager::getInstance()->translate($this->description_key, include_prefix: false);
        }
        return $this->getDescription();
    }
}
