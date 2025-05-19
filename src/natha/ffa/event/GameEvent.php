<?php

namespace natha\ffa\event;

use pocketmine\event\Listener;
use pocketmine\player\Player as PMMPPlayer;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Server;

use natha\ffa\session\SessionFactory;
use natha\ffa\player\Player;
use natha\ffa\FFA;

class GameEvent implements Listener {
  
  public function onEntityDamageEvent(EntityDamageEvent $event): void {
    $victim = $event->getEntity();
    if (!$victim instanceof PMMPPlayer) return;
    $session = SessionFactory::getSession($victim);
    if ($session === null) return;
    $world = Server::getInstance()->getWorldManager()->getWorldByName(FFA::getInstance()->getConfig()->get("ffa-world"));
    if ($victim->getLocation()->equals($world->getSafeSpawn())) {
      $event->cancel();
      return;
    }
    $finalDamage = $event->getFinalDamage();
    $currentHealth = $victim->getHealth();
    if ($finalDamage >= $currentHealth) {
      $event->cancel();
      $victim->teleport($victim->getWorld()->getSafeSpawn());
      $victim->setHealth(20);
      if ($event instanceof EntityDamageByEntityEvent) {
        $damager = $event->getDamager();
        if ($damager instanceof PMMPPlayer && $damager->isOnline()) {
          Player::broadcastKill($damager, $victim);
        }
      } elseif ($event instanceof EntityDamageByChildEntityEvent) {
        $projectile = $event->getDamager();
        if ($projectile instanceof Projectile) {
          $shooter = $projectile->getOwningEntity();
          if ($shooter instanceof PMMPPlayer && $shooter !== $victim && $shooter->isOnline()) {
            Player::broadcastKill($shooter, $victim);
          }
        }
      }
    }
  }
    
  public function onEntityTeleportEvent(EntityTeleportEvent $event): void {
    $entity = $event->getEntity();
    if (!$entity instanceof PMMPPlayer) return;
    $from = $event->getFrom()->getWorld()->getFolderName();
    $to = $event->getTo()->getWorld()->getFolderName();
    $arena = FFA::getInstance()->getConfig()->get("ffa-world");
    if ($from === $arena && $to !== $arena) {
      SessionFactory::removeSession($entity);
    }
  }
  
  public function onQuit(PlayerQuitEvent $event): void {
    $player = $event->getPlayer();
    Player::exitGame($player);
    SessionFactory::removeSession($player);
  }
}
