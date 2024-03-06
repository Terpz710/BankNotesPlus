<?php

declare(strict_types=1);

namespace Terpz710\BankNotesPlus\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

use Terpz710\BankNotesPlus\BankNotesPlus;
use Terpz710\BankNotesPlus\Economy\EconomyManager;

class BankNotesCommand extends Command {

    private $plugin;
    private $economyManager;

    public function __construct(BankNotesPlus $plugin, EconomyManager $economyManager) {
        parent::__construct("banknote", "Convert in-game money into bank notes", "/banknote {amount}");
        $this->setPermission("banknotesplus.cmd");
        $this->plugin = $plugin;
        $this->economyManager = $economyManager;
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
                        $sender->sendMessage(TF::GREEN . "You have converted §f$" . $amount . " §ainto a bank note.");
                    } else {
                        $sender->sendMessage(TF::RED . "Failed to reduce money. Please try again.");
                    }
                });
            } else {
                $sender->sendMessage(TF::RED . "Usage: /banknote {amount}");
            }
        } else {
            $sender->sendMessage(TF::RED . "This command can only be used by players.");
        }
        return true;
    }
}
