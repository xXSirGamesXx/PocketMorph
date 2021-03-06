<?php

namespace pocketmorph\morph\manager;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmorph\PocketMorph;
use pocketmine\Server;
use pocketmine\utils\UUID;

class MorphManager {
	
	private $plugin;
	
	public function __construct(PocketMorph $plugin) {
		$this->plugin = $plugin;
	}
	
	public function moveEntity($player, $entityId) {
		
		$chunk = $player->getLevel()->getChunk($player->x>>4, $player->z>>4);
		
		$player->getLevel()->addEntityMovement(
		$chunk->getX(), $chunk->getZ(),
		$entityId,
		$player->x, $player->y, $player->z,
		$player->yaw, $player->pitch
		);
		
		$pk = new RemoveEntityPacket();
		$pk->entityRuntimeId = $player->getId();
		$pk->clientId = UUID::fromData($player->getId(), $player->getName());
		
		foreach(Server::getInstance()->getOnlinePlayers() as $p) {
			if($p->canSee($player) && $p->getName() !== $player->getName()) {
				$p->dataPacket($pk);
			}
		}
	}
	
	public function createNbt($x, $y, $z, $yaw, $pitch) {
		 $nbt = new CompoundTag;
		
		 $nbt->Pos = new ListTag("Pos", [
			new DoubleTag("", $x),
      			new DoubleTag("", $y),
      			new DoubleTag("", $z)
       		]);

    		$nbt->Rotation = new ListTag("Rotation", [
    			new FloatTag("", $yaw),
    	 		new FloatTag("", $pitch)
       		]);

     		$nbt->Health = new ShortTag("Health", 1);
     		$nbt->Invulnerable = new ByteTag("Invulnerable", 1);

     		return $nbt;
	}
	
	public function removeMorph(Player $player) {
		if($this->isMorphed($player)) {
			$pk = new AddPlayerPacket();
			
			$pk->entityRuntimeId = $player->getId();
			$pk->x = $player->x;
			$pk->y = $player->y;
			$pk->z = $player->z;
			$pk->yaw = $player->yaw;
			$pk->pitch = $player->pitch;
			$pk->username = $player->getName();
			
			foreach(Server::getInstance()->getOnlinePlayers() as $p) {
				if(!$p->canSee($player)) {
					$p->dataPacket($pk);
				}
			}
	
			$this->getMorph($player)->close();
			unset($this->plugin->morphs[$player->getName()]);
		}
		
	}
	
	public function setMorph(Player $player, $morph) {
		$entity = Entity::createEntity($morph, $player->getLevel()->getChunk($player->x >> 4, $player->z >> 4), $this->createNbt($player->x, $player->y, $player->z, $player->yaw, $player->pitch));
   	
	    	foreach(Server::getInstance()->getOnlinePlayers() as $p) {
			if($p->getName() !== $player->getName()) {
				$entity->spawnTo($p);
			}
		}
		$this->plugin->morphs[$player->getName()] = $entity->getId();
		$entity->setNameTag($player->getNameTag());
		
		$pk = new RemovePlayerPacket();
		$pk->entityRuntimeId = $player->getId();
		$pk->clientId = UUID::fromData($player->getId(), $player->getName());
		
		foreach(Server::getInstance()->getOnlinePlayers() as $p) {
			if($p->canSee($player) && $p->getName() !== $player->getName()) {
				$p->dataPacket($pk);
			}
		}
		               
	}
	
	public function getMorph(Player $player) {
		$id = $this->plugin->morphs[$player->getName()];
		$entity = $player->getLevel()->getEntity($id);
		
		return $entity;
	}
	 
	public function isMorphed(Player $player) {
		 return isset($this->plugin->morphs[$player->getName()]);
	}
	
} 
	
