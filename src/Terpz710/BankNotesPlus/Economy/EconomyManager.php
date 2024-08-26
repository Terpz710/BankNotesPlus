<?php

declare(strict_types=1);

namespace Terpz710\BankNotesPlus\Economy;

use Closure;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use onebone\economyapi\EconomyAPI;
use cooldogedev\BedrockEconomy\api\type\ClosureAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\currency\Currency;
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;

use Terpz710\BankNotesPlus\BankNotesPlus;

class EconomyManager {

    private $eco;
    private ?ClosureAPI $api;
    private Currency $currency;
    private BankNotesPlus $plugin;

    public function __construct(BankNotesPlus $plugin) {
        $this->plugin = $plugin;
        $manager = $this->plugin->getServer()->getPluginManager();
        $this->eco = $manager->getPlugin("EconomyAPI") ?? $manager->getPlugin("BedrockEconomy") ?? null;
        $this->api = BedrockEconomyAPI::CLOSURE();
        $this->currency = BedrockEconomy::getInstance()->getCurrency();
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
                $entry = GlobalCache::ONLINE()->get($player->getName());
                $callback($entry ? (float)"{$entry->amount}.{$entry->decimals}" : (float)"{$this->currency->defaultAmount}.{$this->currency->defaultDecimals}");
                break;
            default:
                $entry = GlobalCache::ONLINE()->get($player->getName());
                $callback($entry ? (float)"{$entry->amount}.{$entry->decimals}" : (float)"{$this->currency->defaultAmount}.{$this->currency->defaultDecimals}");
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
                $decimals = (int)(explode('.', strval($amount))[1] ?? 0);
                $this->api->subtract(
                    $player->getXuid(),
                    $player->getName(),
                    (int)$amount,
                    $decimals,
                    fn () => $callback(true),
                    fn () => $callback(false)
                );
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
                $decimals = (int)(explode('.', strval($amount))[1] ?? 0);
                $this->api->add(
                    $player->getXuid(),
                    $player->getName(),
                    (int)$amount,
                    $decimals,
                    fn () => $callback(true),
                    fn () => $callback(false)
                );
                break;
        }
    }
}
