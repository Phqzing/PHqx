<?php

namespace phqzing\phqx\session;

use pocketmine\Server;
use pocketmine\player\Player;
use phqzing\phqx\PHqx;

class Manager {


    private static $players = [];

    public static function insertPlayer(string $name):void
    {
        self::$players[$name] = new PlayerSession();
        PHqx::getInstance()->db->executeInsert("phqx.insert", ["name" => $name]);
    }

    public static function initPlayer(string $name, ?array $killaura = null, ?array $reach = null, ?array $speed = null, ?array $automessage = null, ?bool $antikb = null, ?bool $phase = null, ?bool $taptotp = null, ?bool $cheststealer = null):void
    {
        self::$players[$name] = new PlayerSession($killaura, $reach, $speed, $automessage, $antikb, $phase, $taptotp, $cheststealer);
    }

    
    public static function getPlayer(string $name):?PlayerSession
    {
        return self::$players[$name] ?? null;
    }

    
    public static function savePlayer(string $name):void
    {
        if(is_null(self::getPlayer($name))) return;

        $player = self::getPlayer($name);
        $killaura = json_encode($player->getSettings("killaura"));
        $reach = json_encode($player->getSettings("reach"));
        $speed = json_encode($player->getSettings("speed"));
        $automessage = json_encode($player->getSettings("automessage"));

        PHqx::getInstance()->db->executeChange("phqx.save", [
            "name" => $name,
            "killaura" => $killaura,
            "reach" => $reach,
            "speed" => $speed,
            "automessage" => $automessage,
            "antikb" => $player->getSettings("antikb"),
            "phase" => $player->getSettings("phase"),
            "taptotp" => $player->getSettings("taptotp"),
            "cheststealer" => $player->getSettings("cheststealer")
        ]);

        unset(self::$players[$name]);
    }
}