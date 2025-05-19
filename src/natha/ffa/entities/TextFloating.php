<?php

namespace natha\ffa\entities;

use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\Server;

use natha\surrealdb\SurrealAPI;
use natha\surrealdb\utils\PromiseHandler;

class TextFloating extends Human {
  
  public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
		parent::__construct($location, $skin, $nbt);
		$this->skin = new Skin('Standard_Custom', str_repeat("\x00", 8192), '', 'geometry.humanoid.custom');
		$this->sendSkin();
		$this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
		$this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
	}
  
  public function attack(EntityDamageEvent $event) : void {
		if ($event instanceof EntityDamageByEntityEvent) {
			$event->cancell();
		}
	}
  
  public function onUpdate(int $currentTick): bool {
    SurrealAPI::queries()->selectData(
      "id, kills",
      "player_data",
      "ORDER BY kills DESC LIMIT 10",
      self::class,
      "handleTopKills",
      []
    );
    return parent::onUpdate($currentTick);
  }
  
  public static function handleTopKills(array $result): void {
    PromiseHandler::handle($result,
      function (array $data) {
        $lines = [];
        $i = 1;
        foreach ($data as $row) {
          $name = str_replace("player_data:", "", $row["id"]);
          $kills = $row["kills"];
          $lines[] = "§7[§b#{$i}§7] §f{$name} §7- §e{$kills} Kills";
          if (++$i > 10) break;
        }
        $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
        foreach ($world->getEntities() as $entity) {
          if ($entity instanceof TextFloating) {
            $entity->setNameTag("§bFFA\n§cLEADERBOARD\n" . implode("\n", $lines));
            $entity->setNameTagVisible(true);
            $entity->setNameTagAlwaysVisible(true);
          }
        }
      },
      function (string $error) {
        Server::getInstance()->getLogger()->error("Error al obtener el top kills: " . $error);
      }
    );
  }
}
