<?php

namespace natha\ffa\player;

use pocketmine\player\Player as PMMPPlayer;
use pocketmine\item\VanillaItems;
use pocketmine\item\ItemIds;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\world\WorldManager;
use pocketmine\Server;

use natha\ffa\FFA;
use natha\ffa\session\SessionFactory;
use natha\ffa\scoreboards\Scoreboards;

class Player {
  
  public static function join(PMMPPlayer $player) : void {
    SessionFactory::createSession($player);   
    $worldName = FFA::getInstance()->getConfig()->get("ffa-world");
	  $worldManager = Server::getInstance()->getWorldManager();
    $arena = $worldManager->getWorldByName($worldName);
    if ($arena === null) {
      $player->sendMessage("§cThe world '$worldName' is not available.");
      return;
    }
    $items = [
      VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS())),
      VanillaItems::STEAK()->setCount(32),
		  VanillaItems::GOLDEN_APPLE()->setCount(3),
		  VanillaItems::BOW(),
		  VanillaItems::ARROW()->setCount(16),
    ];
    $helmet = VanillaItems::DIAMOND_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
    $chestplate = VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
	  $leggings = VanillaItems::DIAMOND_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
	  $boots = VanillaItems::DIAMOND_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
    $player->getEffects()->clear();
    $player->getArmorInventory()->clearAll();
    $player->getInventory()->clearAll();
    $player->getCursorInventory()->clearAll();
    $player->getHungerManager()->setEnabled(true);
    $player->getHungerManager()->setFood(20.0);
    $player->setHealth(20.0);
	  $player->teleport($arena->getSafeSpawn());
    foreach ($items as $item) {
      $player->getInventory()->addItem($item);
    }
    $player->getArmorInventory()->setHelmet($helmet);
	  $player->getArmorInventory()->setChestplate($chestplate);
	  $player->getArmorInventory()->setLeggings($leggings);
	  $player->getArmorInventory()->setBoots($boots);
    $player->sendTitle("§bFFA", "§7Kill them all!");
  }
  
  public static function exitGame(PMMPPlayer $player) : bool {
		Scoreboards::removeScoreboard($player);
		$player->getEffects()->clear();
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		$player->getHungerManager()->setEnabled(false);
		$player->getHungerManager()->setFood(20.0);
		$player->setHealth(20.0);
		$player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
		$player->sendMessage("§7You have left §bFFA Game");
		return true;
	}
  
  public static function broadcastKill(PMMPPlayer $killer, PMMPPlayer $victim): void {
    $killerSession = SessionFactory::getSession($killer);
    $victimSession = SessionFactory::getSession($victim);
    $killerSession->addKills();
    $victimSession->addDeaths();
    $killer->sendMessage("§cYou have killed §7{$victim->getName()}");
		foreach ($killer->getWorld()->getPlayers() as $player) {
			$player->sendMessage("§7{$killer->getName()} §chas killed §7{$victim->getName}");
		}
  }
}
