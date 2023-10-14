<?php

namespace Terpz710\BankNotesPlus;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Terpz710\BankNotesPlus\Command\BankNotesCommand;
use davidglitch04\libEco\libEco;

class BankNotesPlus extends PluginBase implements Listener {

    public function onEnable(): void {
        $libEco = new libEco();

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("banknote", new BankNotesCommand($this, $libEco));
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item->getNamedTag()->getTag("Amount") !== null) {
            $amount = $item->getNamedTag()->getFloat("Amount");

            $item->setCount($item->getCount() - 1);
            $player->getInventory()->setItemInHand($item);

            libEco::addMoney($player, $amount);
            $player->sendMessage(TF::GREEN . "You have claimed §f$" . $amount . " §afrom the bank note.");
        }
    }

    public function hasEnoughMoney(Player $player, float $amount): bool {
        return true;
    }

    public function convertToBankNote(Player $player, float $amount): void {
        $bankNote = VanillaItems::PAPER();
        $bankNote->setCustomName(TF::GOLD . "$" . $amount . " Bank Note");
        $bankNote->setLore([
            "Value: $" . $amount,
            "Right-click to redeem"
        ]);
        $bankNote->getNamedTag()->setFloat("Amount", $amount);

        $enchantment = new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 3);
        $bankNote->addEnchantment($enchantment);

        $player->getInventory()->addItem($bankNote);
    }
}
