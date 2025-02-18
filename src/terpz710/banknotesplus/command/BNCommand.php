<?php

declare(strict_types=1);

namespace terpz710\banknotesplus\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;

use pocketmine\player\Player;

use pocketmine\utils\TextFormat as TextColor;

use terpz710\banknotesplus\BankNotesPlus;
use terpz710\banknotesplus\economy\EconomyManager;

class BNCommand extends Command implements PluginOwned {

    protected BankNotesPlus $plugin;
    
    protected EconomyManager $economyManager;

    public function __construct() {
        parent::__construct("banknote");
        $this->setDescription("Convert in-game money into bank notes");
        $this->setUsage("/banknote <amount>");
        $this->setAliases(["bn", "note"]);
        $this->setPermission("banknotesplus.cmd");
        
        $this->plugin = BankNotesPlus::getInstance();
        $this->economyManager = EconomyManager::getInstance();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if (!$this->testPermission($sender)) {
            return true;
        }

        if ($sender instanceof Player) {
            if (count($args) === 1 && is_numeric($args[0]) && $args[0] > 0) {
                $amount = (int)$args[0];

                $this->economyManager->reduceMoney($sender, $amount, function($success) use ($sender, $amount) {
                    if ($success) {
                        $this->plugin->convertToBankNote($sender, $amount);
                        $message = $this->plugin->getConfig()->get("convert_success_message");
                        $message = str_replace("{amount}", (string)$amount, $message);
                        $sender->sendMessage(TextColor::colorize($message));
                    } else {
                        $message = $this->plugin->getConfig()->get("convert_failure_message");
                        $sender->sendMessage(TextColor::colorize($message));
                    }
                });
            } else {
                $message = $this->plugin->getConfig()->get("convert_usage_message");
                $sender->sendMessage(TextColor::colorize($message));
            }
        } else {
            $message = $this->plugin->getConfig()->get("convert_not_player_message");
            $sender->sendMessage(TextColor::colorize($message));
        }
        return true;
    }

    public function getOwningPlugin() : Plugin{
        return $this->plugin;
    }
}
