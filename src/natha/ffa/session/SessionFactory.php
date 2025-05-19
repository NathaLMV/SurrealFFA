<?php

namespace natha\ffa\session;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\scheduler\ClosureTask;
use natha\ffa\scoreboards\Scoreboards;
use natha\surrealdb\SurrealAPI;
use natha\surrealdb\utils\PromiseHandler;
use natha\ffa\FFA;

class SessionFactory {
  
  private static array $sessions = [];
  
  private static function debug(string $msg): void {
    Server::getInstance()->getLogger()->info("[SessionFactory Debug] " . $msg);
  }
  
  public static function createSession(Player $player): void {
    $name = $player->getName();
    SurrealAPI::queries()->selectData(
      "*",
      "player_data",
      "WHERE id = player_data:$name",          
      self::class,
      "handleSelectData",
      ["player" => $name]
    );
  }
  
  public static function handleSelectData(array $result): void {
    PromiseHandler::handle($result,
      function (array $rows, array $extra) {
        $name = $extra['player'];
        $player = Server::getInstance()->getPlayerExact($name);
        if ($player === null) {
          return;
        }
        if (count($rows) === 0) {
          SurrealAPI::queries()->insertData(
            "player_data",
            [
              "id" => $name,
              "kills" => 0,
              "deaths" => 0,
              "lastUpdated" => time()
            ],
            self::class,
            "handleInsertData",
            ["player" => $name]
          );
        } else {
          $data = $rows[0];
          $kills = $data["kills"] ?? 0;
          $deaths = $data["deaths"] ?? 0;
          $lastUpdate = $data["lastUpdated"] ?? time();
          self::$sessions[$name] = new Session($player, $kills, $deaths, $lastUpdate);
        }
      },
      function (string $error, array $extra) {
        self::debug("ERROR selectData para {$extra['player']}: $error");
      }
    );
  }
  
  public static function handleInsertData(array $result): void {
    PromiseHandler::handle($result,
      function ($_, array $extra) {
        $name = $extra['player'];
        $player = Server::getInstance()->getPlayerExact($name);
        if ($player !== null) {
          self::createSession($player);
        }
      },
      function(string $error, array $extra) {
        self::debug("ERROR insertData for {$extra['player']}: $error");
      }
    );
  }

  public static function getSession(Player|string $player) : ?Session {
    $name = $player instanceof Player ? $player->getName() : $player;
    return self::$sessions[$name] ?? null;
  }
  
  public static function getSessionCount(): int {
		return count(self::$sessions);
	}

  public static function saveSessionToDatabase(Player $player, bool $force = false): void {
    $name = $player->getName();
    $session = self::$sessions[$name] ?? null;
    if ($session === null) {
      self::debug("saveSessionToDatabase(): no hay sesión para '$name'");
      return;
    }
    $time = time();
    if ($force || $time - $session->getLastUpdated() >= 30) {
      SurrealAPI::queries()->update(
        "player_data:$name",
        [
          "kills" => $session->getKills(),
          "deaths" => $session->getDeaths(),
          "lastUpdated" => $time
        ],
        "",
        self::class,
        "handleUpdateData",
        ["player" => $name]
        );
      $session->setLastUpdated($time);
    }
  }
  
  public static function handleUpdateData(array $result): void {
    PromiseHandler::handle(
      $result,
      fn($rows, $extra) => self::debug("UPDATE exitoso para {$extra['player']}"),
      fn($err, $extra)  => self::debug("ERROR updateData para {$extra['player']}: $err")
    );
  }

  public static function removeSession(Player $player): void {
    $name = $player->getName();
    self::saveSessionToDatabase($player, true);
    unset(self::$sessions[$name]);
  }
}