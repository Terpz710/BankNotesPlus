<?php

declare(strict_types=1);

namespace terpz710\banknotesplus\command;

use pocketmine\command\CommandSender;

use pocketmine\player\Player;

use pocketmine\utils\TextFormat as TextColor;

use terpz710\banknotesplus\BankNotesPlus;
use terpz710\banknotesplus\economy\EconomyManager;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\IntegerArgument;

class BNCommand extends BaseCommand {

    protected function prepare() : void{
        $this->setPermission("banknotesplus.cmd");
        $this->registerArgument(0, new IntegerArgument("amount", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void{
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextColor::colorize(BankNotesPlus::getInstance()->getConfig()->get("convert_not_player_message")));
            return;
        }

        if (!isset($args["amount"]) || $args["amount"] <= 0) {
            $sender->sendMessage(TextColor::colorize(BankNotesPlus::getInstance()->getConfig()->get("convert_usage_message")));
            return;
        }

        $amount = (int)$args["amount"];
        $economy = EconomyManager::getInstance();
        $plugin = BankNotesPlus::getInstance();

        $economy->reduceMoney($sender, $amount, function($success) use ($sender, $amount, $plugin) {
            if ($success) {
                $plugin->convertToBankNote($sender, $amount);
                $message = $plugin->getConfig()->get("convert_success_message");
                $sender->sendMessage(TextColor::colorize(str_replace("{amount}", (string)$amount, $message)));
            } else {
                $sender->sendMessage(TextColor::colorize($plugin->getConfig()->get("convert_failure_message")));
            }
        });
    }
}
