<?php

namespace phqzing\phqx\session;

class PlayerSession {


    public $settings = [
        "killaura" => [
            "enabled" => false,
            "reach" => 4
        ],
        "reach" => [
            "enabled" => false,
            "amount" => 4
        ],
        "speed" => [
            "enabled" => false,
            "amount" => 0.12
        ],
        "automessage" => [
            "enabled" => false,
            "on-kill" => true,
            "per-second" => false,
            "messages" => []
        ],
        "antikb" => false,
        "phase" => false,
        "taptotp" => false,
        "cheststealer" => false
    ];


    public function __construct(?array $killaura = null, ?array $reach = null, ?array $speed = null, ?array $automessage = null, ?bool $antikb = null, ?bool $phase = null, ?bool $taptotp = null, ?bool $cheststealer = null)
    {
        if(!is_null($killaura)) $this->settings["killaura"] = $killaura;
        if(!is_null($reach)) $this->settings["reach"] = $reach;
        if(!is_null($speed)) $this->settings["speed"] = $speed;
        if(!is_null($automessage)) $this->settings["automessage"] = $automessage;
        if(!is_null($antikb)) $this->settings["antikb"] = $antikb;
        if(!is_null($phase)) $this->settings["phase"] = $phase;
        if(!is_null($taptotp)) $this->settings["taptotp"] = $taptotp;
        if(!is_null($cheststealer)) $this->settings["cheststealer"] = $cheststealer;
    }

    public function getSettings(string $setting):array|bool
    {
        return $this->settings[$setting];
    }

    
    public function isKillAuraEnabled():bool
    {
        return $this->settings["killaura"]["enabled"];
    }

    public function toggleKillAura(bool $val = true):void
    {
        $this->settings["killaura"]["enabled"] = $val;
    }

    public function getKillAuraReach():int
    {
        return $this->settings["killaura"]["reach"];
    }

    public function setKillAuraReach(int $amount = 3):void
    {
        $this->settings["killaura"]["reach"] = $amount;
    }


    public function isReachEnabled():bool
    {
        return $this->settings["reach"]["enabled"];
    }

    public function toggleReach(bool $val = true):void
    {
        $this->settings["reach"]["enabled"] = $val;
    }

    public function getReachAmount():float
    {
        return $this->settings["reach"]["amount"];
    }

    public function setReachAmount(float $amount = 3.0):void
    {
        $this->settings["reach"]["amount"] = $amount;
    }


    public function isSpeedEnabled():bool
    {
        return $this->settings["speed"]["enabled"];
    }

    public function toggleSpeed(bool $val = true):void
    {
        $this->settings["speed"]["enabled"] = $val;
    }

    public function getSpeedAmount():float
    {
        return $this->settings["speed"]["amount"];
    }

    public function setSpeedAmount(float $amount = 0.10):void
    {
        $this->settings["speed"]["amount"] = $amount;
    }


    public function isAutoMessageEnabled():bool
    {
        return $this->settings["automessage"]["enabled"];
    }

    public function toggleAutoMessage(bool $val = true):void
    {
        $this->settings["automessage"]["enabled"] = $val;
    }

    public function getMessages():array
    {
        return $this->settings["automessage"]["messages"];
    }

    public function setMessages(array $messages = []):void
    {
        $this->settings["automessage"]["messages"] = $messages;
    }


    public function isAntiKBEnabled():bool
    {
        return $this->settings["antikb"];
    }

    public function toggleAntiKB(bool $val = true):void
    {
        $this->settings["antikb"] = $val;
    }

    
    public function isPhaseEnabled():bool
    {
        return $this->settings["phase"];
    }

    public function togglePhase(bool $val = true):void
    {
        $this->settings["phase"] = $val;
    }


    public function isTapToTpEnabled():bool
    {
        return $this->settings["taptotp"];
    }

    public function toggleTapToTp(bool $val = true):void
    {
        $this->settings["taptotp"] = $val;
    }


    public function isChestStealerEnabled():bool
    {
        return $this->settings["cheststealer"];
    }

    public function toggleChestStealer(bool $val = true):void
    {
        $this->settings["cheststealer"] = $val;
    }
}