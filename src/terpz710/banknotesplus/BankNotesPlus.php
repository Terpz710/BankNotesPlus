<?php

declare(strict_types=1);

namespace terpz710\banknotesplus;

use pocketmine\plugin\PluginBase;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

use pocketmine\data\bedrock\EnchantmentIdMap;

use pocketmine\player\Player;

use pocketmine\utils\TextFormat as TextColor;

use terpz710\banknotesplus\command\BNCommand;

use CortexPE\Commando\PacketHooker;

class BankNotesPlus extends PluginBase {

    protected static self $instance;

    public const FAKE_ENCH_ID = -1;

    protected function onLoad() : void{
        self::$instance = $this;
    }
        
    protected function onEnable() : void{
        $this->saveDefaultConfig();
        
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
        
        $this->getServer()->getCommandMap()->register("BankNotesPlus", new BNCommand($this, "banknote", "Convert in-game money into bank notes", ["bn", "note"]));

        EnchantmentIdMap::getInstance()->register(
            self::FAKE_ENCH_ID,
            new Enchantment("Glow", 1, ItemFlags::ALL, ItemFlags::NONE, 1)
        );
    }

    public static function getInstance() : self{
        return self::$instance;
    }

    public function convertToBankNote(Player $player, int $amount, int $quantity = 1) : void{
        $bankNote = $this->getBankNote($amount, $quantity);
        $player->getInventory()->addItem($bankNote);
    }

    public function getBankNote(int $amount, int $quantity = 1) : ?Item{
        $bankNote = StringToItemParser::getInstance()->parse($this->getConfig()->get("bank_note_item"));
        $customName = $this->getConfig()->get("bank_note_name");
        $customName = str_replace("{amount}", (string)$amount, $customName);
        $bankNote->setCustomName(TextColor::colorize($customName));
        $lore = $this->getConfig()->get("bank_note_lore");
        $lore = array_map(function($line) use ($amount) {
            return TextColor::colorize(str_replace("{amount}", (string)$amount, $line));
        }, $lore);
        $bankNote->setLore($lore);
        $bankNote->setCount($quantity);
        $bankNote->getNamedTag()->setInt("Amount", $amount);
        $bankNote->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(self::FAKE_ENCH_ID), 1));
        return $bankNote;
    }
}
