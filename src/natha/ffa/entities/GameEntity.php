<?php

namespace natha\ffa\entities;

use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player as PMMPPlayer;

use natha\ffa\player\Player;
use natha\ffa\session\SessionFactory;

class GameEntity extends Human {
  
  public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
		parent::__construct($location, $skin, $nbt);
		$this->skin = $skin;
		$this->sendSkin();
	}
  
  public function attack(EntityDamageEvent $event) : void {
		if ($event instanceof EntityDamageByEntityEvent) {
			if ($event->getDamager() instanceof PMMPPlayer) {
				Player::join($event->getDamager());
				return;
			}
		}
	}
  
  public function onUpdate(int $currentTick): bool {
    $this->setNameTag("§bFFA\n§7Player: §r".SessionFactory::getSessionCount());
    $this->setNameTagVisible(true);
    $this->setNameTagAlwaysVisible(true);
    return parent::onUpdate($currentTick);
  }
}