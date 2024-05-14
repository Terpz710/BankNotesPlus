<?php

declare(strict_types=1);

namespace Terpz710\BankNotesPlus\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;
use pocketmine\player\Player;

use Terpz710\BankNotesPlus\BankNotesPlus;
use Terpz710\BankNotesPlus\Economy\EconomyManager;

class BNCommand extends Command implements PluginOwned {

    private $plugin;
    private $economyManager;

    public function __construct(BankNotesPlus $plugin, EconomyManager $economyManager) {
        parent::__construct("banknote", "Convert in-game money into bank notes", "/banknote {amount}");
        $this->setPermission("banknotesplus.cmd");
        $this->plugin = $plugin;
        $this->economyManager = $economyManager;
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
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
                        $sender->sendMessage($message);
                    } else {
                        $message = $this->plugin->getConfig()->get("convert_failure_message");
                        $sender->sendMessage($message);
                    }
                });
            } else {
                $message = $this->plugin->getConfig()->get("convert_usage_message");
                $sender->sendMessage($message);
            }
        } else {
            $message = $this->plugin->getConfig()->get("convert_not_player_message");
            $sender->sendMessage($message);
        }
        return true;
    }
}
