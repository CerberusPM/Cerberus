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

use pocketmine\command\CommandSender;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\RawStringArgument;

use CerberusPM\Cerberus\utils\ConfigManager;
use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\utils\LandManager;

/**
 * A class which provides /cerberus reload command functionality
 */
class ReloadSubcommand extends BaseSubCommand {

    private ConfigManager $config_manager;
    private LangManager $lang_manager;
    private LandManager $land_manager;

    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("all|config|lang|land", true)); //Optional. If not specified what to reload will reload everything
        
        $this->setPermission("cerberus.command.reload");
        
        $this->config_manager = ConfigManager::getInstance();
        $this->lang_manager = LangManager::getInstance();
        $this->land_manager = LandManager::getInstance();
    }
    
    public function onRun(CommandSender $sender, string $alias, array $args): void {
        $arg = $args["all|config|lang|land"] ?? null;
        $old_lang = $this->config_manager->get("language") ?? "eng";
        switch($arg) {
            case null:
            case "all":
                $this->config_manager->reload();
                $this->lang_manager->reload();
                $count = $this->land_manager->loadLandclaims();
                $sender->sendMessage($this->lang_manager->translate("command.reload.all"));
                $sender->sendMessage($this->lang_manager->translate("command.reload.land", [$count]));
                break;
            case "config":
            case "conf":
                $this->config_manager->reload();
                $sender->sendMessage($this->lang_manager->translate("command.reload.config"));
                break;
            case "language":
            case "lang":
                $this->lang_manager->reload();
                $sender->sendMessage($this->lang_manager->translate("command.reload.lang", [$this->lang_manager->translate("language-name")]));
                return; //Skip language change check
            case "landclaims":
            case "land":
                $count = $this->land_manager->loadLandclaims();
                $sender->sendMessage($this->lang_manager->translate("command.reload.land", [$count]));
                break;
            default:
                $sender->sendMessage($this->lang_manager->translate("command.reload.options"));
                return; //Skip language check change
                
        }
        //Language change check and lang_manager reload if changed
        $new_lang = $this->config_manager->get("language");
        if ($old_lang !== $new_lang && isset($new_lang)) {
                $this->lang_manager->reload(); //Reload the language file as new language is set in the config
                $sender->sendMessage($this->lang_manager->translate("command.reload.lang.switch",
                        [$this->lang_manager->translate("lang.$old_lang", include_prefix: false),
                            $this->lang_manager->translate("lang.$new_lang", include_prefix: false)]));
        }
    }
} 
