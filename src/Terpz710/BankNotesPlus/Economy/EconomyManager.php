<?php

declare(strict_types=1);

namespace Terpz710\Sell\Economy;

use Closure;
use pocketmine\player\Player;

use onebone\economyapi\EconomyAPI;
use cooldogedev\BedrockEconomy\api\type\ClosureAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\currency\Currency;
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;

use Terpz710\Sell\Main;

class EconomyManager {

    private $eco;
    private ?ClosureAPI $api;
    private Currency $currency;
    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $manager = $this->plugin->getServer()->getPluginManager();
        $this->eco = $manager->getPlugin("EconomyAPI") ?? $manager->getPlugin("BedrockEconomy") ?? null;

        if ($this->eco instanceof BedrockEconomy) {
            $this->api = BedrockEconomyAPI::CLOSURE();
            $this->currency = BedrockEconomy::getInstance()->getCurrency();
        }

        if ($this->eco === null) {
            $this->plugin->getLogger()->warning("ERROR: No compatible economy plugin found. Please install EconomyAPI or BedrockEconomy");
        }
    }

    public function getMoney(Player $player, Closure $callback): void {
        if ($this->eco === null) {
            $callback(0.0);
            return;
        }

        switch ($this->eco->getName()) {
            case "EconomyAPI":
                $money = $this->eco->myMoney($player->getName());
                $callback(is_numeric($money) ? (float)$money : 0.0);
                break;
            case "BedrockEconomy":
                $entry = GlobalCache::ONLINE()->get($player->getName());
                $callback($entry ? (float)"{$entry->amount}.{$entry->decimals}" : (float)"{$this->currency->defaultAmount}.{$this->currency->defaultDecimals}");
                break;
            default:
                $callback(0.0);
        }
    }

    public function addMoney(Player $player, int $amount, Closure $callback): void {
        if ($this->eco === null) {
            $this->plugin->getLogger()->warning("You don't have an Economy plugin");
            $callback(false);
            return;
        }

        switch ($this->eco->getName()) {
            case "EconomyAPI":
                $callback($this->eco->addMoney($player->getName(), $amount) === EconomyAPI::RET_SUCCESS);
                break;
            case "BedrockEconomy":
                $decimals = (int)(explode('.', strval($amount))[1] ?? 0);
                $this->api->add(
                    $player->getXuid(),
                    $player->getName(),
                    (int)$amount,
                    $decimals,
                    fn() => $callback(true),
                    fn() => $callback(false)
                );
                break;
            default:
                $callback(false);
        }
    }
}
