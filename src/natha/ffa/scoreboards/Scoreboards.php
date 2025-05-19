<?php

namespace natha\ffa\scoreboards;

use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class Scoreboards {
  
  private static array $active = [];

  private static array $lines = [];

  private static function getObjectiveName(Player $player): string {
    return "FFA_" . strtolower($player->getName());
  }

  public static function hasScoreboard(Player $player): bool {
    return isset(self::$active[$player->getName()]);
  }

  public static function createScoreboard(Player $player, string $displayName, int $sortOrder = 0, string $displaySlot = "sidebar"): void {
    $name = $player->getName();
    if (isset(self::$active[$name])) {
      self::removeScoreboard($player);
    }
    $objectiveName = self::getObjectiveName($player);
    $pk = new SetDisplayObjectivePacket();
    $pk->displaySlot   = $displaySlot;
    $pk->objectiveName = $objectiveName;
    $pk->displayName   = $displayName;
    $pk->criteriaName  = "dummy";
    $pk->sortOrder     = $sortOrder;
    $player->getNetworkSession()->sendDataPacket($pk);
    self::$active[$name] = $objectiveName;
    self::$lines[$name]  = [];
  }
  
  public static function removeScoreboard(Player $player): void {
    $name = $player->getName();
    if (!isset(self::$active[$name])) return;
    $pk = new RemoveObjectivePacket();
    $pk->objectiveName = self::$active[$name];
    $player->getNetworkSession()->sendDataPacket($pk);
    unset(self::$active[$name], self::$lines[$name]);
  }
  
  public static function addLine(Player $player, string $text, int $score): void {
    $name = $player->getName();
    if (!isset(self::$active[$name])) {
      return;
    }
    $objectiveName = self::$active[$name];
    $entry = new ScorePacketEntry();
    $entry->objectiveName = $objectiveName;
    $entry->type          = ScorePacketEntry::TYPE_FAKE_PLAYER;
    $entry->customName    = $text;
    $entry->score         = $score;
    $entry->scoreboardId  = $score;
    $pk = new SetScorePacket();
    $pk->type    = SetScorePacket::TYPE_CHANGE;
    $pk->entries = [$entry];
    $player->getNetworkSession()->sendDataPacket($pk);
    self::$lines[$name][$score] = $text;
  }

  public static function removeLines(Player $player): void {
    $name = $player->getName();
    if (empty(self::$lines[$name])) {
      return;
    }
    $objectiveName = self::$active[$name] ?? null;
    if ($objectiveName === null) return;
    foreach (self::$lines[$name] as $score => $_) {
      $entry = new ScorePacketEntry();
      $entry->objectiveName = $objectiveName;
      $entry->score         = $score;
      $entry->scoreboardId  = $score;
      $pk = new SetScorePacket();
      $pk->type    = SetScorePacket::TYPE_REMOVE;
      $pk->entries = [$entry];
      $player->getNetworkSession()->sendDataPacket($pk);
    }
    self::$lines[$name] = [];
  }

  public static function updateLines(Player $player, array $lines): void {
    self::removeLines($player);
    foreach ($lines as $score => $text) {
      self::addLine($player, $text, (int)$score);
    }
  }
}
