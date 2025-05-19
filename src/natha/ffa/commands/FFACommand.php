<?php

namespace natha\ffa\commands;

use pocketmine\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\nbt\tag\CompoundTag;

use natha\ffa\scoreboards\Scoreboards;
use natha\ffa\player\Player as FFAPlayer;
use natha\ffa\FFA;
use natha\ffa\entities\TextFloating;
use natha\ffa\entities\GameEntity;

class FFACommand extends VanillaCommand {
  
  public function __construct(public  FFA $plugin) {
    $this->setPermission("ffa.command.use");
    $this->setPermission("ffa.dev.use");
		parent::__construct('ffa',"ffa command help",'use /ffa help', ['ffa']);
	}
  
  public function execute(CommandSender $sender, string $label, array $args): bool {
    if (!$sender->hasPermission("ffa.command.use")) {
			$sender->sendMessage("§cYou are not allowed to use FFA commands.");
			return true;
		}
		if (!isset($args[0])) {
			$sender->sendMessage("§eUse: §6/ffa join, /ffa exit, /ffa help");
			return true;
		}
    $subCommand = strtolower($args[0]);
		switch ($subCommand) {
      case "join":
        if ($sender instanceof Player) {
          FFAPlayer::join($sender);
				} else {
          $sender->sendMessage("§cOnly players can use this command.");
				}
				break;
			case "exit":
				if ($sender instanceof Player) {
					FFAPlayer::exitGame($sender);
				} else {
					$sender->sendMessage("§cOnly players can use this command.");
				}
				break;
      case "help":
        if ($sender instanceof Player) {
          $this->getHelp($sender);
        } else {
					$sender->sendMessage("§cOnly players can use this command.");
				}
        break;
			case "npc":
        if (!$sender->hasPermission("ffa.dev.use")) {
					$sender->sendMessage("§cYou don't have permission to use advanced features.");
					return true;
				}
        $nbt = CompoundTag::create();
        $entity = new GameEntity($sender->getLocation(), $sender->getSkin(),$nbt);
        $entity->spawnToAll();
        break;
			case "tops":
				if (!$sender->hasPermission("ffa.dev.use")) {
					$sender->sendMessage("§cYou don't have permission to use advanced features.");
					return true;
				}
        $nbt = CompoundTag::create();
        $entity = new TextFloating($sender->getLocation(), $sender->getSkin(),$nbt);
        $entity->spawnToAll();
				break;
			default:
				$sender->sendMessage("§cUnknown subcommand: $sub");
				break;
    }
    return false;
  }
  
  private function getHelp(Player $player) : bool {
		if ($this->plugin->getServer()->isOp($player->getName())) {
			$player->sendMessage("§7-====(§bFFA §aHelp§7)====-\n" .
        "§e/ffa help: §7Shows ffa help\n" .
		    "§e/ffa join: §7Join ffa game\n" .
		    "§e/ffa arena <arena>: §7Sets ffa arena\n" .
		    "§e/ffa npc: §7Sets the game npc\n" .
		    "§e/ffa tops: §7Sets tops npc\n" .
		    "§e/ffa exit: §7Exit from ffa\n");
    } else {
			$player->sendMessage("§7-====(§bFFA §aHelp§7)====-\n" .
        "§e/ffa help: §7Shows ffa help\n" .
	      "§e/ffa join: §7Join ffa game\n");
		}
		return true;
	}
}
