<?php

namespace natha\ffa\scoreboards;

use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

use natha\ffa\session\SessionFactory;
use natha\ffa\FFA;

class ScoreboardFactory {
  
  public static function init(): void {
    FFA::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void {
      $arena = strtolower(FFA::getInstance()->getConfig()->get("ffa-world"));
      foreach (Server::getInstance()->getOnlinePlayers() as $player) {
        $name = $player->getName();
        $inArena = strtolower($player->getWorld()->getFolderName()) === $arena;
        $hasBoard = Scoreboards::hasScoreboard($player);
        $session = SessionFactory::getSession($player);
        if (!$inArena) {
          if ($hasBoard) {
            Scoreboards::removeScoreboard($player);
          }
          continue;
        }
        if (!$hasBoard) {
          Scoreboards::createScoreboard($player, "§b§lFFA");
          if ($session === null) {
            Scoreboards::addLine($player, "§7Cargando datos...", 1);
            continue;
          }
        }
        if ($session !== null) {
          $kills  = $session->getKills();
          $deaths = $session->getDeaths();
          Scoreboards::updateLines($player,[
            1 => str_repeat("-", 10),
            2 => "§6Kills§7: §f" . $kills,
            3 => "§6Deaths§7: §f" . $deaths,
            4 => "§6KDR§7: §f" . ($deaths > 0 ? round($kills / $deaths, 2) : $kills),
            5 => str_repeat("-", 10)
          ]);
        }
      }
    }), 20);
  }
}