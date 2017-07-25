<?php
namespace pocketmorph\command;

use pocketmorph\PocketMorph;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class Commands {
	
	public $plugin;
	
	public function __construct(PocketMorph $plugin) {
		$this->plugin = $plugin;
	}
	
	public function getPlugin() {
		return $this->plugin;
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		$types = [
		 "Chicken",
		 "Pig",
		 "Sheep",
		 "Cow",
		 "Mooshroom",
		 "Wolf",
		 "Enderman",
		 "Spider",
		 "Skeleton",
		 "PigZombie",
		 "Creeper",
		 "Slime",
		 "Silverfish",
		 "Villager",
		 "Zombie",
		 "Player",
		 "Squid",
		 "Bat",
		 "CaveSpider",
		 "MagmaCube",
		 "Ghast",
		 "Ocelot",
		 "Blaze",
		 "ZombieVillager",
		 "Snowman",
		 ];
							 
		if(strtolower($cmd->getName()) == "morph") {
			if($sender->hasPermission("morph.command")) {
				
				if(count($args) > 0) {
					if(!isset($args[0])) {
						$sender->sendMessage(TextFormat::GREEN . "Type '/morph help' for a list of commands");
						return true;
					} else {						
						if(strtolower($args[0]) == "list") {
							$sender->sendMessage(TextFormat::GREEN . "Entities: " . implode(',', $types));
							return true;
						}
						
						if(strtolower($args[0]) == "remove") {
							if($this->getPlugin()->getMorphManager()->isMorphed($sender)) {
								$sender->sendMessage(TextFormat::GREEN . "Morph removed");
								$this->getPlugin()->getMorphManager()->removeMorph($sender); 
								return true;
							}
						}
						
						if(strtolower($args[0]) == "help") {
							$sender->sendMessage(TextFormat::GREEN . "PocketMorph help\n- /morph help\n- /morph remove\n- /morph <entity>\n- /morph list");
							return true;
						}
						
						foreach ($types as $type) {								 
							if($args[0] == $type) {
								$typeStr = "Morph" . $args[0];
								if($this->getPlugin()->getMorphManager()->isMorphed($sender)) {
									$this->getPlugin()->getMorphManager()->removeMorph($sender); 
									$this->getPlugin()->getMorphManager()->setMorph($sender, $typeStr);
									$sender->sendMessage(TextFormat::GREEN . "You morphed into a " . $args[0]);
									return true;
								 } else {
									$this->getPlugin()->getMorphManager()->setMorph($sender, $typeStr);
									$sender->sendMessage(TextFormat::GREEN . "You morphed into a " . $args[0]);
									return true;
								 }
							 } 
						}

						if(strtolower($args[0]) !== "help" && strtolower($args[0]) !== "remove" && strtolower($args[0]) !== "list") {
							if(!in_array($args[0], $types)) {
								$sender->sendMessage(TextFormat::RED . "No such entity, type '/morph list' for a list of all available entities, or '/morph help' for a list of commands");
								return true;
								
							}
						}
					}
				}
			}
		}
		return true;
	}
}
	
