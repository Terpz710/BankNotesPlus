<?php

declare(strict_types=1);

namespace terpz710\banknotesplus\economy;

use Closure;

use pocketmine\player\Player;

use pocketmine\utils\SingletonTrait;

use terpz710\banknotesplus\BankNotesPlus;

use onebone\economyapi\EconomyAPI;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\currency\Currency;
use cooldogedev\BedrockEconomy\api\type\ClosureAPI;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;

final class EconomyManager {
    use SingletonTrait;

    protected BankNotesPlus $plugin;

    private ?ClosureAPI $api;
    
    private ?Currency $currency;

    public function __construct() {
        $this->plugin = BankNotesPlus::getInstance();
        $manager = $this->plugin->getServer()->getPluginManager();
        $this->eco = $manager->getPlugin("EconomyAPI") ?? $manager->getPlugin("BedrockEconomy") ?? null;

        if ($this->eco instanceof BedrockEconomy) {
            $this->api = BedrockEconomyAPI::CLOSURE();
            $this->currency = BedrockEconomy::getInstance()->getCurrency();
        } else {
            $this->api = null;
            $this->currency = null;
        }

        unset($manager);
    }

    public function getMoney(Player $player, Closure $callback) : void{
        if ($this->eco instanceof EconomyAPI) {
            $money = $this->eco->myMoney($player->getName());
            assert(is_float($money));
            $callback($money);
        } elseif ($this->eco instanceof BedrockEconomy) {
            $entry = GlobalCache::ONLINE()->get($player->getName());
            $callback($entry ? (float)"{$entry->amount}.{$entry->decimals}" : (float)"{$this->currency->defaultAmount}.{$this->currency->defaultDecimals}");
        } else {
            $callback(0.0);
        }
    }

    public function reduceMoney(Player $player, int $amount, Closure $callback) : void{
        if ($this->eco === null) {
            $this->plugin->getLogger()->warning("You don't have an Economy plugin");
            return;
        }

        if ($this->eco instanceof EconomyAPI) {
            $callback($this->eco->reduceMoney($player->getName(), $amount) === EconomyAPI::RET_SUCCESS);
        } elseif ($this->eco instanceof BedrockEconomy) {
            $decimals = (int)(explode('.', strval($amount))[1] ?? 0);
            $this->api->subtract(
                $player->getXuid(),
                $player->getName(),
                (int)$amount,
                $decimals,
                function() use ($callback) {
                    $callback(true);
                },
                function() use ($callback) {
                    $callback(false);
                }
            );
        }
    }

    public function addMoney(Player $player, int $amount, Closure $callback) : void{
        if ($this->eco === null) {
            $this->plugin->getLogger()->warning("You don't have an Economy plugin");
            return;
        }

        if ($this->eco instanceof EconomyAPI) {
            $callback($this->eco->addMoney($player->getName(), $amount) === EconomyAPI::RET_SUCCESS);
        } elseif ($this->eco instanceof BedrockEconomy) {
            $decimals = (int)(explode('.', strval($amount))[1] ?? 0);
            $this->api->add(
                $player->getXuid(),
                $player->getName(),
                (int)$amount,
                $decimals,
                function() use ($callback) {
                    $callback(true);
                },
                function() use ($callback) {
                    $callback(false);
                }
            );
        }
    }
}
