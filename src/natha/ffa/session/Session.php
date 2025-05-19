<?php

namespace natha\ffa\session;

use pocketmine\player\Player;

class Session {
  
  private int $kills = 0;
  private int $deaths = 0;
  private int $lastUpdated = 0;
  
  public function __construct(private Player $player, int $kills = 0, int $deaths = 0, int $lastUpdated = 0) {
    $this->kills = $kills;
    $this->deaths = $deaths;
    $this->lastUpdated = $lastUpdated;
  }
  
  public function getPlayer() : Player {
    return $this->player;
  }
  
  public function getKills() : int {
    return $this->kills;
  }
  
  public function addKills() : void {
    $this->kills++;
  }
  
  public function getDeaths() : int {
    return $this->deaths;
  }
  
  public function addDeaths() : void  {
    $this->deaths++;
  }
  
   public function getLastUpdated() : int {
    return $this->lastUpdated;
  }

  public function setLastUpdated(int $time) : void {
    $this->lastUpdated = $time;
  }
}
