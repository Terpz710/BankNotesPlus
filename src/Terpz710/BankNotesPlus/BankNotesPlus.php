<?php

declare(strict_types=1);

namespace Terpz710\BankNotesPlus;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

use Terpz710\BankNotesPlus\Command\BankNotesCommand;
use Terpz710\BankNotesPlus\Economy\EconomyManager;

class BankNotesPlus extends PluginBase implements Listener {

    private $economyManager;
    private $config;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        
        $this->economyManager = new EconomyManager($this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("banknote", new BankNotesCommand($this, $this->economyManager));
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item->getNamedTag()->getTag("Amount") !== null) {
            $amount = $item->getNamedTag()->getInt("Amount");
            $item->setCount($item->getCount() - 1);
            $player->getInventory()->setItemInHand($item);
            $this->economyManager->addMoney($player, $amount, function($success) use ($player, $amount) {
                if ($success) {
                    $message = $this->config->get("claim_message");
                    $message = str_replace("{amount}", (string)$amount, $message);
                    $player->sendMessage($message);
                } else {
                    $message = $this->config->get("failure_message");
                    $player->sendMessage($message);
                }
            });
        }
    }

    public function convertToBankNote(Player $player, int $amount): void {
        $bankNote = VanillaItems::PAPER();
        $bankNote->setCustomName(str_replace("{amount}", (string)$amount, $this->config->get("bank_note_name")));
        $lore = [
            $this->config->get("bank_note_value_line"),
            $this->config->get("bank_note_lore")
        ];
        $lore = array_map(function($line) use ($amount) {
            return str_replace("{amount}", (string)$amount, $line);
        }, $lore);
        $bankNote->setLore($lore);
        $bankNote->getNamedTag()->setInt("Amount", $amount);
        $enchantment = new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 3);
        $bankNote->addEnchantment($enchantment);
        $player->getInventory()->addItem($bankNote);
    }
}
