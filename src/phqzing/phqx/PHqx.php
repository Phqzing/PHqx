<?php

namespace phqzing\phqx;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\math\{Vector3, VoxelRayTrace};
use pocketmine\item\enchantment\MeleeWeaponEnchantment;
use pocketmine\entity\animation\{ArmSwingAnimation, CriticalHitAnimation};
use pocketmine\world\sound\{EntityAttackNoDamageSound, EntityAttackSound, ItemBreakSound};
use pocketmine\item\Durable;
use pocketmine\entity\Living;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent};
use pocketmine\event\player\PlayerExhaustEvent;

use phqzing\phqx\libs\poggit\libasynql\libasynql;
use phqzing\phqx\libs\dktapps\pmforms\{CustomForm, CustomFormResponse};
use phqzing\phqx\libs\dktapps\pmforms\element\{Label, Slider, Input, Toggle};

use phqzing\phqx\tasks\AutoMessageTask;
use phqzing\phqx\session\Manager;

class PHqx extends PluginBase {


    private static $instance;
    private static $columns = [
        "killaura",
        "reach",
        "speed",
        "automessage",
        "antikb",
        "phase",
        "taptotp",
        "cheststealer"
    ];
    public $db;


    public function onEnable():void
    {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $this->db = libasynql::create($this, $this->getConfig()->get("database"), ["sqlite" => "settings.sql"]);
        $this->db->executeGeneric("phqx.init");
        
        // checking if there is a missing column (for future updates)
        foreach(self::$columns as $i => $column)
        {
            $this->db->executeSelect("phqx.check", ["column" => $column], function(array $val)use($column)
            {
                if($val[0]["CNTREC"] == 0)
                {
                    $this->db->executeGeneric("phqx.addcolumn.{$column}");
                    $this->getLogger()->info("§aCrated §6{$column}");
                }
            });
        }

        $this->getServer()->getPluginManager()->registerEvents(new PHqxListener($this), $this);
    }

    public function onDisable():void
    {
        foreach($this->getServer()->getOnlinePlayers() as $player)
        {
            if(!is_null(Manager::getPlayer($player->getName())))
            {
                Manager::savePlayer($player->getName());
            }
        }
        if(isset($this->db)) $this->db->close();
    }

    public static function getInstance():PHqx
    {
        return self::$instance;
    }


    
    public function sendForm(Player $player, string $form)
    {
        $player_session = Manager::getPlayer($player->getName());
        if(is_null($player_session)) return;

        switch(strtolower($form))
        {
            case "killaura":
                $form = new CustomForm(
                    "§l§3Kill Aura Settings",
                    [
                        new Slider("kaslider", "§3Kill Aura Reach", 3, 20, 1, $player_session->getKillAuraReach())
                    ],
                    function(Player $player, CustomFormResponse $data)use($player_session):void
                    {
                        $value = $data->getFloat("kaslider");
                        $player_session->setKillAuraReach((int)$value);
                        $player->sendMessage("§8[§2PHQX§8] §3Kill Aura reach has been set to §e{$value}");
                    }
                );

                $player->sendForm($form);
            break;


            case "reach":
                $form = new CustomForm(
                    "§l§3Reach Settings",
                    [
                        new Label("reachlabel", "§7Note: The value is capped at 20 to ensure that you don't go overboard and end up lagging the server\n\nDefault Value: 3.0\n\n§8Note (for PE players): This will only properly work if you enable split controls"),
                        new Input("reachinput", "§3Reach Amount", "must be a number", $player_session->getReachAmount())
                    ],
                    function(Player $player, CustomFormResponse $data)use($player_session):void
                    {
                        $value = $data->getString("reachinput");

                        if(!is_numeric($value))
                        {
                            $player->sendMessage("§8[§4PHQX§8] §3Invalid reach amount given! amount must be numeric");
                            return;
                        }
                        if((int)$value >= 21 or (int)$value <= 3)
                        {
                            $player->sendMessage("§8[§4PHQX§8] §3Invalid reach amount given! amount must be less than 21 and more than 3");
                            return;
                        }
                        if($value == $player_session->getReachAmount()) return;

                        $player_session->setReachAmount((float)$value);
                        $player->sendMessage("§8[§2PHQX§8] §3Reach amount has been set to §e{$value}");
                    }
                );

                $player->sendForm($form);
            break;

            case "speed":
                $form = new CustomForm(
                    "§l§3Speed Settings",
                    [
                        new Label("speedlabel", "§7Note: This is capped at 3 because if you go above 3 it causes movement issues and other stuff\n\nDefault Value: 0.10"),
                        new Input("speedinput", "§3Speed Amount", "must be a number", $player_session->getSpeedAmount())
                    ],
                    function(Player $player, CustomFormResponse $data)use($player_session):void
                    {
                        $value = $data->getString("speedinput");

                        if(!is_numeric($value))
                        {
                            $player->sendMessage("§8[§4PHQX§8] §3Invalid speed amount given! amount must be numeric");
                            return;
                        }
                        if($value > 3 or $value <= 0.10)
                        {
                            $player->sendMessage("§8[§4PHQX§8] §3Invalid speed amount given! amount must be less than or equals to 3 and more than 0.10");
                            return;
                        }
                        if($value == $player_session->getSpeedAmount()) return;

                        $player_session->setSpeedAmount((float)$value);
                        $player->setMovementSpeed((float)$value);
                        $player->sendMessage("§8[§2PHQX§8] §3Speed amount has been set to §e{$value}");
                    }
                );

                $player->sendForm($form);
            break;

            case "automessage":
                $form = new CustomForm(
                    "§l§3Speed Settings",
                    [
                        new Label("automessagelabel", "§7Note: You can add multiple messages by separating them with (:) for example: ".'"'."Hello:Hi:Gg:Gf:Have fun!".'".'),
                        new Input("automessageinput", "§3Message(s)", "", implode(":", $player_session->getMessages())),
                        new Toggle("automessagetoggle1", "§3Message On Kill", $player_session->settings["automessage"]["on-kill"]),
                        new Toggle("automessagetoggle2", "§3Message Per Second", $player_session->settings["automessage"]["per-second"])
                    ],
                    function(Player $player, CustomFormResponse $data)use($player_session):void
                    {
                        $msges = explode(":", $data->getString("automessageinput"));
                        $on_kill = $data->getBool("automessagetoggle1");
                        $per_second = $data->getBool("automessagetoggle2");

                        $player_session->setMessages($msges);
                        $player_session->settings["automessage"]["on-kill"] = $on_kill;
                        if($player_session->settings["automessage"]["per-second"] != $per_second)
                        {
                            if($per_second) $this->getScheduler()->scheduleRepeatingTask(new AutoMessageTask($this, $player->getName()), 20);
                            $player_session->settings["automessage"]["per-second"] = $per_second;
                        }
                        $player->sendMessage("§8[§2PHQX§8] §3Auto Message successfully updated");
                    }
                );

                $player->sendForm($form);
            break;
        }
    }



    public function raycast(Player $player, Vector3 $start, Vector3 $end, float $radius = 3.0):?Player
    {
        $hitEntity = null;
        $hitBlock = null;

        foreach(VoxelRayTrace::betweenPoints($start, $end) as $vector3)
        {
			$block = $player->getWorld()->getBlockAt($vector3->x, $vector3->y, $vector3->z);
			$result = $block->calculateIntercept($start, $end);
			if(!is_null($result))
            {
                $hitBlock = $block->getPosition();
                break;
            }
		}

        foreach($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius - 1, $radius - 1 , $radius - 1)) as $entity)
        {
            if($entity instanceof Player)
            {
                if($entity->getName() == $player->getName()) continue;
                $result = $entity->getBoundingBox()->calculateIntercept($start, $end);
                if(is_null($result)) continue;
                $hitEntity = $entity;
                break;
            }
        }

        if(!is_null($hitEntity) and !is_null($hitBlock))
        {
            $blockDist = $player->getPosition()->distance($hitBlock);
            $entityDist = $player->getPosition()->distance($hitEntity->getPosition());

            if($entityDist < $blockDist) return $hitEntity;
            return null;
        }

        return $hitEntity;
    }


    /*
     this is taken from the pocketmine Player class but modified it a little bit
      - doesnt check reach/interact distance
    */
    public function attackEntity(Player $attacker, Player $victim):void
    {
        if(!$victim->isAlive() or !$victim->isConnected() or !$attacker->isAlive() or !$attacker->isConnected()) return;

        $heldItem = $attacker->getInventory()->getItemInHand();
        $oldItem = clone $heldItem;

        $ev = new EntityDamageByEntityEvent($attacker, $victim, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $heldItem->getAttackPoints());
        if($attacker->isSpectator() or !$this->getServer()->getConfigGroup()->getConfigBool("pvp")) $ev->cancel();

        $meleeEnchantmentDamage = 0;
		$meleeEnchantments = [];
		foreach($heldItem->getEnchantments() as $enchantment)
        {
			$type = $enchantment->getType();
			if($type instanceof MeleeWeaponEnchantment && $type->isApplicableTo($victim))
            {
				$meleeEnchantmentDamage += $type->getDamageBonus($enchantment->getLevel());
				$meleeEnchantments[] = $enchantment;
			}
		}
		$ev->setModifier($meleeEnchantmentDamage, EntityDamageEvent::MODIFIER_WEAPON_ENCHANTMENTS);

		if(!$attacker->isSprinting() and !$attacker->isFlying() and $attacker->getFallDistance() > 0 and !$attacker->getEffects()->has(VanillaEffects::BLINDNESS()) and !$attacker->isUnderwater()) 
        {    
            $ev->setModifier($ev->getFinalDamage() / 2, EntityDamageEvent::MODIFIER_CRITICAL);
        }

		$victim->attack($ev);
		$attacker->broadcastAnimation(new ArmSwingAnimation($attacker), $attacker->getViewers());

		$soundPos = $victim->getPosition()->add(0, $victim->getSize()->getHeight() / 2, 0);
		if($ev->isCancelled())
        {
			$attacker->getWorld()->addSound($soundPos, new EntityAttackNoDamageSound());
			return;
		}
		$attacker->getWorld()->addSound($soundPos, new EntityAttackSound());

		if($ev->getModifier(EntityDamageEvent::MODIFIER_CRITICAL) > 0 && $victim instanceof Living)
        {
			$victim->broadcastAnimation(new CriticalHitAnimation($victim));
		}

		foreach($meleeEnchantments as $enchantment)
        {
			$type = $enchantment->getType();
			assert($type instanceof MeleeWeaponEnchantment);
			$type->onPostAttack($attacker, $victim, $enchantment->getLevel());
		}

		if($attacker->isAlive())
        {
			if($heldItem->onAttackEntity($victim) && $attacker->hasFiniteResources() && $oldItem->equalsExact($attacker->getInventory()->getItemInHand()))
            {
				if($heldItem instanceof Durable && $heldItem->isBroken())
                {
					$attacker->broadcastSound(new ItemBreakSound());
				}
				$attacker->getInventory()->setItemInHand($heldItem);
			}

			$attacker->getHungerManager()->exhaust(0.1, PlayerExhaustEvent::CAUSE_ATTACK);
		}
    }
}