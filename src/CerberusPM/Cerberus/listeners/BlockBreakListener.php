<?php
declare(strict_types=1);

namespace CerberusPM\Cerberus\listeners;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use CerberusPM\Cerberus\Cerberus;
use CerberusPM\Cerberus\utils\ConfigManager;
use CerberusPM\Cerberus\utils\LangManager;
use CerberusPM\Cerberus\utils\LandManager;

/**
 * A temporary class used for block break registration
 * Will be then replaced by flags
 */
class BlockBreakListener implements Listener {
    
    private Cerberus $plugin;
    private ConfigManager $config_manager;
    private LangManager $lang_manager;
    private LandManager $land_manager;

    function __construct(Cerberus $plugin) {
        $this->plugin = $plugin;
        $this->config_manager = $plugin->getConfigManager();
        $this->lang_manager = $plugin->getLangManager();
        $this->land_manager = $plugin->getLandManager();
    }


    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $position = $player->getPosition(); // Get the player's current position

        $landclaims = $this->land_manager->getLandClaimsByPosition($position);

        foreach ($landclaims as $land) {
            if (!$land->isOwner($player) && !$land->isMember($player)) {
                $event->cancel(true); // Cancel the event for non-owners or non-members
            }
        }
    }
}
