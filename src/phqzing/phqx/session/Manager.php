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

    public static function initPlayer(string $name, ?array $killaura = null, ?array $reach = null, ?array $speed = null, bool $antikb = false):void
    {
        self::$players[$name] = new PlayerSession($killaura, $reach, $speed, $antikb);
        if(is_null($speed) and ($player = Server::getInstance()->getPlayerExact($name)) instanceof Player) $player->setMovementSpeed(0.12);
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

        PHqx::getInstance()->db->executeChange("phqx.save", [
            "name" => $name,
            "killaura" => $killaura,
            "reach" => $reach,
            "speed" => $speed,
            "antikb" => $player->getSettings("antikb")
        ]);

        unset(self::$players[$name]);
    }
}