<?php

namespace Terpz710\BankNotesPlus\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Terpz710\BankNotesPlus\BankNotesPlus;
use davidglitch04\libEco\libEco;

class BankNotesCommand extends Command {

    private $plugin;
    private $libEco;

    public function __construct(BankNotesPlus $plugin, libEco $libEco) {
        parent::__construct("banknote", "Convert in-game money into bank notes", "/banknote {amount}");
        $this->setPermission("banknotesplus.cmd");
        $this->plugin = $plugin;
        $this->libEco = $libEco;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$this->testPermission($sender)) {
            return true;
        }

        if ($sender instanceof Player) {
            if (count($args) === 1 && is_numeric($args[0]) && $args[0] > 0) {
                $amount = (float)$args[0];

                $this->libEco->myMoney($sender, function($balance) use ($sender, $amount) {
                    if ($balance >= $amount) {
                        $this->libEco->reduceMoney($sender, $amount, function($success) use ($sender, $amount) {
                            if ($success) {
                                $this->plugin->convertToBankNote($sender, $amount);
                                $sender->sendMessage(TF::GREEN . "You have converted §f$" . $amount . " §ainto a bank note.");
                            } else {
                                $sender->sendMessage(TF::RED . "Failed to reduce money. Please try again.");
                            }
                        });
                    } else {
                        $sender->sendMessage(TF::RED . "You don't have enough money to convert into a bank note.");
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
