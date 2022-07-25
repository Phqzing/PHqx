<?php

namespace phqzing\phqx\tasks;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;

use phqzing\phqx\PHqx;
use phqzing\phqx\session\Manager;

class AutoMessageTask extends Task {


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
            if(in_array($player->getWorld()->getFolderName(), $this->plugin->getConfig()->get("black-listed-worlds"))) throw new CancelTaskException();
            if(!is_null($player_session = Manager::getPlayer($this->name)) and $player_session->isAutoMessageEnabled() and !empty($msges = $player_session->getMessages()) and $player_session->settings["automessage"]["per-second"])
            {
                $msg = $msges[array_rand($msges)];
                $player->chat($msg);
            }else{
                throw new CancelTaskException();
            }
        }else{
            throw new CancelTaskException();
        }
    }
}