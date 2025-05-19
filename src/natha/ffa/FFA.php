<?php

namespace natha\ffa;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\EntityDataHelper as Helper;
use pocketmine\world\World;

use natha\ffa\scoreboards\ScoreboardFactory;
use natha\ffa\event\GameEvent;
use natha\ffa\commands\FFACommand;
use natha\surrealdb\SurrealAPI;
use natha\surrealdb\utils\PromiseHandler;
use natha\ffa\entities\TextFloating;
use natha\ffa\entities\GameEntity;

class FFA extends PluginBase {
  
  public static ?FFA $instance = null;
  
  public function onEnable() : void {
    self::$instance = $this;
    $this->saveResource("config.yml");
    $query = SurrealAPI::queries();
    $query->createTable("player_data", self::class, "handleResult");
    $line = str_repeat("=", 60);
    $logger = $this->getLogger();
    $logger->info("{$line}");
    $logger->info("§bFFA Plugin V1.0.0");
    $logger->info("§aPocketMine Version§7: §r".Server::getInstance()->getPocketMineVersion());
    $logger->info("§aAuthor§7: §rNATHA");
    $logger->info("§aPHP Version§7: §r".PHP_VERSION);
    $logger->info("§Start time§7: §r".date("H:i:s"));
    $logger->info("§aDATABASE TYPE§7: §rSurrealDB");
    $logger->info("{$line}");
    Server::getInstance()->getPluginManager()->registerEvents(new GameEvent(), $this);
    Server::getInstance()->getCommandMap()->register('ffa', new FFACommand($this), 'ffa');
    ScoreboardFactory::init();
    $worldName = $this->getConfig()->get("ffa-world");
    Server::getInstance()->getWorldManager()->loadWorld($worldName);
    EntityFactory::getInstance()->register(TextFloating::class, function(World $world, CompoundTag $nbt) : TextFloating {
      return new TextFloating(Helper::parseLocation($nbt, $world),TextFloating::parseSkinNBT($nbt), $nbt);
    }, ['textfloating', 'ffa:textfloating']);
    EntityFactory::getInstance()->register(GameEntity::class, function(World $world, CompoundTag $nbt) : GameEntity {
      return new GameEntity(Helper::parseLocation($nbt, $world),GameEntity::parseSkinNBT($nbt), $nbt);
    }, ['gameentity', 'ffa:gameentity']);
  }
  
  public static function getInstance() : ?FFA {
    return self::$instance;
  }
  
  public static function handleResult(array $result): void {
    PromiseHandler::handle($result,
      function ($rows, $extra) {
        var_dump($rows);
      },
      function ($error, $extra) {
      }
    );
  }
}
