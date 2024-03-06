<?php

declare(strict_types=1);

namespace Terpz710\BankNotesPlus\Economy;

use Closure;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use cooldogedev\BedrockEconomy\api\legacy\ClosureContext;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\type\LegacyAPI;
use onebone\economyapi\EconomyAPI;
use Terpz710\BankNotesPlus\BankNotesPlus;

class EconomyManager {
    /** @var Plugin|null $eco */
    private ?Plugin $eco;
    /** @var LegacyAPI|null $api */
    private ?LegacyAPI $api;
    /** @var BankNotesPlus $plugin */
    private BankNotesPlus $plugin;

    public function __construct(BankNotesPlus $plugin) {
        $this->plugin = $plugin;
        $manager = $plugin->getServer()->getPluginManager();
        $this->eco = $manager->getPlugin("EconomyAPI") ?? $manager->getPlugin("BedrockEconomy") ?? null;
        $this->api = BedrockEconomyAPI::legacy();
        unset($manager);
    }

    public function getMoney(Player $player, Closure $callback): void {
        switch ($this->eco->getName()) {
            case "EconomyAPI":
                $money = $this->eco->myMoney($player->getName());
                assert(is_float($money));
                $callback($money);
                break;
            case "BedrockEconomy":
                $this->api->getPlayerBalance($player->getName(), ClosureContext::create(static function(?int $balance) use($callback) : void {
                    $callback($balance ?? 0);
                }));
                break;
            default:
                $this->api->getPlayerBalance($player->getName(), ClosureContext::create(static function(?int $balance) use($callback) : void {
                    $callback($balance ?? 0);
                }));
        }
    }

    public function reduceMoney(Player $player, int $amount, Closure $callback): void {
        if ($this->eco == null) {
            $this->plugin->getLogger()->warning("You don't have an Economy plugin");
            return;
        }
        switch ($this->eco->getName()) {
            case "EconomyAPI":
                $callback($this->eco->reduceMoney($player->getName(), $amount) === EconomyAPI::RET_SUCCESS);
                break;
            case "BedrockEconomy":
                $this->api->subtractFromPlayerBalance($player->getName(), (int) ceil($amount), ClosureContext::create(static function(bool $success) use($callback) : void {
                    $callback($success);
                }));
                break;
        }
    }

    public function addMoney(Player $player, int $amount, Closure $callback): void {
        if ($this->eco == null) {
            $this->plugin->getLogger()->warning("You don't have an Economy plugin");
            return;
        }
        switch ($this->eco->getName()) {
            case "EconomyAPI":
                $callback($this->eco->addMoney($player->getName(), $amount, EconomyAPI::RET_SUCCESS));
                break;
            case "BedrockEconomy":
                $this->api->addToPlayerBalance($player->getName(), (int) ceil($amount), ClosureContext::create(static function(bool $success) use($callback) : void {
                    $callback($success);
                }));
                break;
        }
    }
}