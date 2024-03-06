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
use pocketmine\utils\TextFormat as TF;

use Terpz710\BankNotesPlus\Command\BankNotesCommand;
use Terpz710\BankNotesPlus\Economy\EconomyManager;

class BankNotesPlus extends PluginBase implements Listener {

    private $economyManager;

    public function onEnable(): void {
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
                    $player->sendMessage(TF::GREEN . "You have claimed §f$" . $amount . " §afrom the bank note.");
                } else {
                    $player->sendMessage(TF::RED . "Failed to add money. Please try again.");
                }
            });
        }
    }

    public function convertToBankNote(Player $player, int $amount): void {
        $bankNote = VanillaItems::PAPER();
        $bankNote->setCustomName(TF::GOLD . "$" . $amount . " Bank Note");
        $bankNote->setLore([
            "Value: $" . $amount,
            "Right-click to redeem"
        ]);
        $bankNote->getNamedTag()->setInt("Amount", $amount);
        $enchantment = new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 3);
        $bankNote->addEnchantment($enchantment);
        $player->getInventory()->addItem($bankNote);
    }
}
