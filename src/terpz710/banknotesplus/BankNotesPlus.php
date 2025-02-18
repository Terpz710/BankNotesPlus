<?php

declare(strict_types=1);

namespace terpz710\banknotesplus;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

use pocketmine\data\bedrock\EnchantmentIdMap;

use pocketmine\player\Player;

use terpz710\banknotesplus\command\BNCommand;

class BankNotesPlus extends PluginBase {

    protected static self $instance;

    public const FAKE_ENCH_ID = -1;

    protected function onLoad() : void{
        self::$instance = $this;
    }
        
    protected function onEnable() : void{
        $this->saveDefaultConfig();
        
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        
        $this->getServer()->getCommandMap()->register("BankNotesPlus", new BNCommand());

        EnchantmentIdMap::getInstance()->register(
            self::FAKE_ENCH_ID,
            new Enchantment("Glow", 1, ItemFlags::ALL, ItemFlags::NONE, 1)
        );
    }

    public static function getInstance() : self{
        return self::$instance;
    }

    public function onPlayerInteract(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();
        $item = $event->getItem();
        $action = $event->getAction();

        if ($action === PlayerInteractEvent::LEFT_CLICK_BLOCK || $action === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            if ($item->getNamedTag()->getTag("Amount")) {
                $amount = $item->getNamedTag()->getInt("Amount");
                $item->setCount($item->getCount() - 1);
                $player->getInventory()->setItemInHand($item);
                $this->economyManager->addMoney($player, $amount, function($success) use ($player, $amount, $event) {
                    if ($success) {
                        $message = $this->getConfig()->get("claim_message");
                        $message = str_replace("{amount}", (string)$amount, $message);
                        $player->sendMessage($message);
                    } else {
                        $message = $this->getConfig()->get("failure_message");
                        $player->sendMessage($message);
                        $event->cancel();
                    }
                });
            }
        }
    }

    public function convertToBankNote(Player $player, int $amount) : void{
        $bankNote = $this->getBankNote($amount);
        $player->getInventory()->addItem($bankNote);
    }

    private function getBankNote(int $amount) : ?Item{
        $bankNote = StringToItemParser::getInstance()->parse($this->getConfig()->get("bank_note_item"));
        $bankNote->setCustomName(str_replace("{amount}", (string)$amount, $this->getConfig()->get("bank_note_name")));
        $lore = [
            $this->getConfig()->get("bank_note_value_line"),
            $this->getConfig()->get("bank_note_lore")
        ];
        $lore = array_map(function($line) use ($amount) {
            return str_replace("{amount}", (string)$amount, $line);
        }, $lore);
        $bankNote->setLore($lore);
        $bankNote->getNamedTag()->setInt("Amount", $amount);
        $bankNote->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(self::FAKE_ENCH_ID), 1));
        return $bankNote;
    }
}
