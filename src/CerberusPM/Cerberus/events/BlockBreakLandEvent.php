<?php
declare(strict_types=1);

namespace CerberusPM\Cerberus\events;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use CerberusPM\Cerberus\Cerberus;
use CerberusPM\Cerberus\CerberusAPI;
use CerberusPM\Cerberus\utils\ConfigManager;
use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\utils\SelectionManager;
use CerberusPM\Cerberus\EventListener;

class BlockBreakLandEvent implements Listener {
    private Cerberus $plugin;
    private CerberusAPI $api;
    private ConfigManager $config_manager;
    private LangManager $lang_manager;

    function __construct(Cerberus $plugin) {
        $this->plugin = $plugin;
        $this->api = $plugin->getAPI();
        $this->config_manager = $plugin->getConfigManager();
        $this->lang_manager = $plugin->getLangManager();
    }


    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $position = $player->getPosition(); // Get the player's current position

        $landclaims = $this->api->getLandClaimsByPosition($position);

        foreach ($landclaims as $land) {
            if ($player != $land->getOwner()) { // TODO: whitelist check
                $event->cancel(true); // Cancel the event for other players
            }
        }
    }
}
