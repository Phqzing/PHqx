<?php

namespace phqzing\phqx;

use pocketmine\event\Listener;
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityTeleportEvent};
use pocketmine\event\player\{PlayerQuitEvent, PlayerChatEvent, PlayerMoveEvent};
use pocketmine\player\Player;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

use phqzing\phqx\tasks\KillAuraTask;
use phqzing\phqx\session\Manager;

class PHqxListener implements Listener {


    private $plugin;
    private $formqueue = [];

    
    public function __construct(PHqx $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onQuit(PlayerQuitEvent $ev):void    
    {
        $player = $ev->getPlayer();
        $player->setMovementSpeed(0.10);
        Manager::savePlayer($player->getName());
    }

    public function onChat(PlayerChatEvent $ev):void
    {
        $player = $ev->getPlayer();

        switch(strtolower($ev->getMessage()))
        {
            case ".help":
                if(!$player->hasPermission("phqzing.phqx.cheats")) return;
                $ev->cancel();
                $player->sendMessage("§b[§3All PHqx Commands§b]\n §8- §e.inject §7(Activates the cheat and loads all your previous settings)\n §8- §e.eject §7(Deactivates the cheat and saves your settings)\n §8- §e.toggle killaura §7(Turns on or off kill aura)\n §8- §e.killaura edit §7(Shows a form UI where you can edit your kill aura settings)\n §8- §e.toggle reach §7(Turns on or off reach)\n §8- §e.reach edit §7(Shows a form UI where you can edit your reach settings)\n §8- §e.toggle speed §7(Turns on or off speed)\n §8- §e.speed edit §7(Shows a form UI where you can edit your speed settings)\n §8- §e.toggle antikb §7(Turns on or off antikb)");
            break;

            case ".inject":
                if(!$player->hasPermission("phqzing.phqx.cheats")) return;
                $ev->cancel();
                if(in_array($player->getWorld()->getFolderName(), $this->plugin->getConfig()->get("black-listed-worlds")))
                {
                    $player->sendMessage("§8[§4PHQX§8] §3This plugin has been §cdisabled §3in this world");
                    return;
                }
                if(!is_null(Manager::getPlayer($player->getName()))) return;
                PHqx::getInstance()->db->executeSelect("phqx.get", ["name" => $player->getName()], function(array $rows)use($player){
                    if(empty($rows))
                    {
                        Manager::insertPlayer($player->getName());
                    }else{
                        foreach($rows as $result)
                        {
                            $killaura = (isset($result["killaura"]) and $result["killaura"] != "none") ? json_decode($result["killaura"], true) : null;
                            $reach = (isset($result["reach"]) and $result["reach"] != "none") ? json_decode($result["reach"], true) : null;
                            $speed = (isset($result["speed"]) and $result["speed"] != "none") ? json_decode($result["speed"], true) : null;
                            $antikb = $result["antikb"] ?? null;
                        }
            
                        if(!is_null($speed) and $speed["enabled"] and !in_array($player->getWorld()->getFolderName(), $this->plugin->getConfig()->get("black-listed-worlds"))) 
                        {
                            $player->setMovementSpeed($speed["amount"]);
                        }

                        Manager::initPlayer($player->getName(), $killaura, $reach, $speed, $antikb);
                    }
                });
                $player->sendMessage("§8[§2PHQX§8] §3Successfully §ainjected §3and loaded all your settings");
            break;

            case ".eject":
                if(!$player->hasPermission("phqzing.phqx.cheats")) return;
                $ev->cancel();
                if(!is_null(Manager::getPlayer($player->getName())))
                {
                    Manager::savePlayer($player->getName());
                    $player->setMovementSpeed(0.10);
                    $player->sendMessage("§8[§2PHQX§8] §3Successfully §cejected");
                }else{
                    $player->sendMessage("§8[§4PHQX§8] §3Cheat was never §ainjected §3in the first place!");
                }
            break;

            case ".ka edit":
            case ".killaura edit":
                if(!$player->hasPermission("phqzing.phqx.cheats")) return;
                $ev->cancel();
                if(is_null(Manager::getPlayer($player->getName())))
                {
                    $player->sendMessage("§8[§4PHQX§8] §3You must §ainject §3before you can edit settings or toggle cheats");
                    return;
                }
                $player->sendMessage("§8[§6PHQX§8] §3The form ui will popup once you close the chat and move a little");
                $this->formqueue[$player->getName()] = "killaura";
            break;
            
            case ".toggle ka":
            case ".toggle killaura":
                if(!$player->hasPermission("phqzing.phqx.cheats")) return;
                $ev->cancel();
                if(is_null(Manager::getPlayer($player->getName())))
                {
                    $player->sendMessage("§8[§4PHQX§8] §3You must §ainject §3before you can edit settings or toggle cheats");
                    return;
                }
                if(Manager::getPlayer($player->getName())->isKillAuraEnabled())
                {
                    $player->sendMessage("§8[§4PHQX§8] §3Kill Aura has been turned §cOFF");
                    Manager::getPlayer($player->getName())->toggleKillAura(false);
                }else{
                    $player->sendMessage("§8[§2PHQX§8] §3Kill Aura has been turned §aON");
                    Manager::getPlayer($player->getName())->toggleKillAura(true);
                    $this->plugin->getScheduler()->scheduleRepeatingTask(new KillAuraTask($this->plugin, $player->getName()), $this->plugin->getConfig()->get("killaura-tickrate"));
                }
            break;


            case ".reach edit":
                if(!$player->hasPermission("phqzing.phqx.cheats")) return;
                $ev->cancel();
                if(is_null(Manager::getPlayer($player->getName())))
                {
                    $player->sendMessage("§8[§4PHQX§8] §3You must §ainject §3before you can edit settings or toggle cheats");
                    return;
                }
                $player->sendMessage("§8[§6PHQX§8] §3The form ui will popup once you close the chat and move a little");
                $this->formqueue[$player->getName()] = "reach";
            break;

            case ".toggle reach":
                if(!$player->hasPermission("phqzing.phqx.cheats")) return;
                $ev->cancel();
                if(is_null(Manager::getPlayer($player->getName())))
                {
                    $player->sendMessage("§8[§4PHQX§8] §3You must §ainject §3before you can edit settings or toggle cheats");
                    return;
                }
                if(Manager::getPlayer($player->getName())->isReachEnabled())
                {
                    $player->sendMessage("§8[§4PHQX§8] §3Reach has been turned §cOFF");
                    Manager::getPlayer($player->getName())->toggleReach(false);
                }else{
                    $player->sendMessage("§8[§2PHQX§8] §3Reach has been turned §aON");
                    Manager::getPlayer($player->getName())->toggleReach(true);
                }
            break;


            case ".speed edit":
                if(!$player->hasPermission("phqzing.phqx.cheats")) return;
                $ev->cancel();
                if(is_null(Manager::getPlayer($player->getName())))
                {
                    $player->sendMessage("§8[§4PHQX§8] §3You must §ainject §3before you can edit settings or toggle cheats");
                    return;
                }
                $player->sendMessage("§8[§6PHQX§8] §3The form ui will popup once you close the chat and move a little");
                $this->formqueue[$player->getName()] = "speed";
            break;

            case ".toggle speed":
                if(!$player->hasPermission("phqzing.phqx.cheats")) return;
                $ev->cancel();
                if(is_null(Manager::getPlayer($player->getName())))
                {
                    $player->sendMessage("§8[§4PHQX§8] §3You must §ainject §3before you can edit settings or toggle cheats");
                    return;
                }
                if(Manager::getPlayer($player->getName())->isSpeedEnabled())
                {
                    $player->sendMessage("§8[§4PHQX§8] §3Speed has been turned §cOFF");
                    $player->setMovementSpeed(0.10);
                    Manager::getPlayer($player->getName())->toggleSpeed(false);
                }else{
                    $player->sendMessage("§8[§2PHQX§8] §3Speed has been turned §aON");
                    $player->setMovementSpeed(Manager::getPlayer($player->getName())->getSpeedAmount());
                    Manager::getPlayer($player->getName())->toggleSpeed(true);
                }
            break;


            case ".toggle antikb":
                if(!$player->hasPermission("phqzing.phqx.cheats")) return;
                $ev->cancel();
                if(is_null(Manager::getPlayer($player->getName())))
                {
                    $player->sendMessage("§8[§4PHQX§8] §3You must §ainject §3before you can edit settings or toggle cheats");
                    return;
                }
                if(Manager::getPlayer($player->getName())->isAntiKBEnabled())
                {
                    $player->sendMessage("§8[§4PHQX§8] §3AntiKB has been turned §cOFF");
                    Manager::getPlayer($player->getName())->toggleAntiKB(false);
                }else{
                    $player->sendMessage("§8[§2PHQX§8] §3AntiKB has been turned §aON");
                    Manager::getPlayer($player->getName())->toggleAntiKB(true);
                }
            break;
        }
    }


    public function onTeleport(EntityTeleportEvent $ev):void
    {
        $player = $ev->getEntity();

        if(!($player instanceof Player)) return;

        if(!is_null(Manager::getPlayer($player->getName())) and in_array($ev->getTo()->getWorld()->getFolderName(), $this->plugin->getConfig()->get("black-listed-worlds")))
        {
            Manager::savePlayer($player->getName());
            $player->sendMessage("§8[§4PHQX§8] §3Auto §cejected §3because the plugin is §cdisabled §3in this world");
            return;
        }
    }


    public function onHit(EntityDamageByEntityEvent $ev):void
    {
        $player = $ev->getEntity();

        if(!($player instanceof Player)) return;

        if(!is_null($player_session = Manager::getPlayer($player->getName())) and $player_session->isAntiKBEnabled())
        {
	    if(in_array($player->getWorld()->getFolderName(), $this->plugin->getConfig()->get("black-listed-worlds"))) return;
            $ev->setKnockback(0);
            return;
        }
    }


    public function onMove(PlayerMoveEvent $ev):void
    {
        $player = $ev->getPlayer();

        if(isset($this->formqueue[$player->getName()]))
        {
            $this->plugin->sendForm($player, $this->formqueue[$player->getName()]);
            unset($this->formqueue[$player->getName()]);
            $ev->cancel();
            return;
        }
    }


    public function onDataReceive(DataPacketReceiveEvent $event):void
    {
        $packet = $event->getPacket();
        if($packet::NETWORK_ID == LevelSoundEventPacket::NETWORK_ID and $packet instanceof LevelSoundEventPacket) 
        {
            $player = $event->getOrigin()->getPlayer();
            if(in_array($player->getWorld()->getFolderName(), $this->plugin->getConfig()->get("black-listed-worlds"))) return;
            if($player instanceof Player and $packet->sound == LevelSoundEvent::ATTACK_NODAMAGE)
            {
                if(!is_null($player_session = Manager::getPlayer($player->getName())) and $player_session->isReachEnabled())
                {
                    $start = $player->getLocation()->asVector3()->add(0, $player->getEyeHeight(), 0);
                    $radius = $player_session->getReachAmount();
                    $end =  $start->addVector($player->getDirectionVector()->multiply($radius));

                    $result = $this->plugin->raycast($player, $start, $end, $radius);
                    if(is_null($result)) return;

                    $this->plugin->attackEntity($player, $result);
                }
            }
        }
    }
}
