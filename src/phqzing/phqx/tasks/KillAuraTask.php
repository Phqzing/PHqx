<?php

namespace phqzing\phqx\tasks;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\entity\animation\ArmSwingAnimation;


use phqzing\phqx\PHqx;
use phqzing\phqx\session\Manager;

class KillAuraTask extends Task {


    private $plugin;
    private $name;


    public function __construct(PHqx $plugin, string $name)
    {
        $this->plugin = $plugin;
        $this->name = $name;
    }


    public function onRun():void
    {
        if(($player = $this->plugin->getServer()->getPlayerExact($this->name)) instanceof Player)
        {
            if(!is_null($player_session = Manager::getPlayer($this->name)) and $player_session->isKillAuraEnabled())
            {
                $radius = $player_session->getKillAuraReach();
                foreach($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $entity)
                {
                    if($entity instanceof Player)
                    {
                        if($entity->getName() == $player->getName()) continue;
                        $player->broadcastAnimation(new ArmSwingAnimation($player));
                        $this->plugin->attackEntity($player, $entity);
                    }
                }
            }else{
                throw new CancelTaskException();
            }
        }else{
            throw new CancelTaskException();
        }
    }
}