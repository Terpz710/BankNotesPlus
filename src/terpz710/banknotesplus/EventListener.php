<?php

declare(strict_types=1);

namespace terpz710\banknotesplus;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;

use terpz710\banknotesplus\economy\EconomyManager;

class EventListener implements Listener {

    public function onPlayerInteract(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();
        $item = $event->getItem();
        $action = $event->getAction();

        if ($action === PlayerInteractEvent::LEFT_CLICK_BLOCK || $action === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            if ($item->getNamedTag()->getTag("Amount")) {
                $amount = $item->getNamedTag()->getInt("Amount");
                $item->setCount($item->pop());
                $player->getInventory()->setItemInHand($item);
                EconomyManager::getInstance()->addMoney($player, $amount, function($success) use ($player, $amount, $event) {
                    if ($success) {
                        $message = BankNotesPlus::getInstance()->getConfig()->get("claim_message");
                        $message = str_replace("{amount}", (string)$amount, $message);
                        $player->sendMessage($message);
                    } else {
                        $message = BankNotesPlus::getInstance()->getConfig()->get("failure_message");
                        $player->sendMessage($message);
                        $event->cancel();
                    }
                });
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event) : void{
        if ($event->getItem()->getNamedTag()->getTag("Amount")) {
            $event->cancel();
        }
}
