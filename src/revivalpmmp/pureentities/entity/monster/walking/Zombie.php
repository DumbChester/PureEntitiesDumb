<?php
declare(strict_types=1);

/**
 * PureEntitiesX: Mob AI Plugin for PMMP
 * Copyright (C)  2018 RevivalPMMP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace revivalpmmp\pureentities\entity\monster\walking;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Level;
use revivalpmmp\pureentities\components\BreedingComponent;
use revivalpmmp\pureentities\components\MobEquipment;
use revivalpmmp\pureentities\data\Data;
use revivalpmmp\pureentities\entity\monster\Monster;
use revivalpmmp\pureentities\entity\monster\WalkingMonster;
use revivalpmmp\pureentities\features\IntfCanBreed;
use revivalpmmp\pureentities\features\IntfCanEquip;
use revivalpmmp\pureentities\PureEntities;
use revivalpmmp\pureentities\traits\Breedable;
use revivalpmmp\pureentities\traits\Feedable;
use revivalpmmp\pureentities\utils\MobDamageCalculator;

//use pocketmine\event\Timings;

class Zombie extends WalkingMonster implements IntfCanEquip, IntfCanBreed, Monster{


	use Breedable, Feedable;
	const NETWORK_ID = Data::NETWORK_IDS["zombie"];

	/**
	 * @var MobEquipment
	 */
	private $mobEquipment;

	/**
	 * Not a complete list yet ...
	 *
	 * @var array
	 */
	private $pickUpLoot = [ItemIds::IRON_SWORD, ItemIds::IRON_SHOVEL];

	public function initEntity() : void{
		parent::initEntity();
		$this->speed = 1.1;
		$this->setDamage([0, 2, 3, 4]);

		$this->mobEquipment = new MobEquipment($this);
		$this->mobEquipment->init();

		$this->feedableItems = [];

		$this->breedableClass = new BreedingComponent($this);
		$this->breedableClass->init();
	}

	/**
	 * Returns the appropriate NetworkID associated with this entity
	 * @return int
	 */
	public function getNetworkId(){
		return self::NETWORK_ID;
	}

	public function getName() : string{
		return "Zombie";
	}

	public function setHealth(float $amount) : void{
		parent::setHealth($amount);

		if($this->isAlive()){
			if(15 < $this->getHealth()){
				$this->setDamage([0, 2, 3, 4]);
			}else if(10 < $this->getHealth()){
				$this->setDamage([0, 3, 4, 6]);
			}else if(5 < $this->getHealth()){
				$this->setDamage([0, 3, 5, 7]);
			}else{
				$this->setDamage([0, 4, 6, 9]);
			}
		}
	}

	/**
	 * Zombie gets attacked. We need to recalculate the damage done with reducing the damage by armor type.
	 *
	 * @param EntityDamageEvent $source
	 */
	public function attack(EntityDamageEvent $source) : void{
		$damage = $this->getDamage();
		PureEntities::logOutput("$this: attacked with original damage of $damage");
		$reduceDamagePercent = 0;
		if($this->getMobEquipment() !== null){
			$reduceDamagePercent = $this->getMobEquipment()->getArmorDamagePercentToReduce();
		}
		if($reduceDamagePercent > 0){
			$reduceBy = $damage * $reduceDamagePercent / 100;
			PureEntities::logOutput("$this: reduce damage by $reduceBy");
			$damage = $damage - $reduceBy;
		}

		PureEntities::logOutput("$this: attacked with final damage of $damage");

		parent::attack($source);
	}

	/**
	 * This zombie attacks a player
	 *
	 * @param Entity $player
	 */
	public function attackEntity(Entity $player){
		if($this->attackDelay > 10 && $this->distanceSquared($player) < 2){
			$this->attackDelay = 0;
			// maybe this needs some rework ... as it should be calculated within the event class and take
			// mob's weapon into account. for now, i just add the damage from the weapon the mob wears
			$damage = $this->getDamage();
			if($this->getMobEquipment() !== null){
				$damage = $damage + $this->getMobEquipment()->getWeaponDamageToAdd();
			}
			$ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK,
				MobDamageCalculator::calculateFinalDamage($player, $damage));
			$player->attack($ev);

			$this->checkTamedMobsAttack($player);
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->isClosed()) return false;
		//Timings::$timerEntityBaseTick->startTiming();

		$this->getMobEquipment()->entityBaseTick($tickDiff);

		$hasUpdate = parent::entityBaseTick($tickDiff);

		$time = $this->getLevel() !== null ? $this->getLevel()->getTime() % Level::TIME_FULL : Level::TIME_NIGHT;
		if(
			!$this->isOnFire()
			&& ($time < Level::TIME_NIGHT || $time > Level::TIME_SUNRISE)
		){
			$this->setOnFire(100);
		}
		//Timings::$timerEntityBaseTick->stopTiming();
		return $hasUpdate;
	}

	public function getDrops() : array{
		$drops = [];
		if($this->isLootDropAllowed()){
			array_push($drops, Item::get(Item::ROTTEN_FLESH, 0, mt_rand(0, 2)));
			// 2.5 percent chance of dropping one of these items.
			if(mt_rand(1, 1000) % 25 === 0){
				switch(mt_rand(1, 3)){
					case 1:
						array_push($drops, Item::get(Item::CARROT, 0, 1));
						break;
					case 2:
						array_push($drops, Item::get(Item::POTATO, 0, 1));
						break;
					case 3:
						array_push($drops, Item::get(Item::IRON_INGOT, 0, 1));
						break;
				}
			}
		}
		return $drops;
	}

	public function getMaxHealth() : int{
		return 20;
	}

	public function updateXpDropAmount() : void{
		if($this->getBreedingComponent()->isBaby()){
			$this->xpDropAmount = 12;
			return;
		}
		$this->xpDropAmount = 5;
	}


	// -------------------- equipment methods --------------------

	/**
	 * @return MobEquipment
	 */
	public function getMobEquipment() : MobEquipment{
		return $this->mobEquipment;
	}

	/**
	 * @return array
	 */
	public function getPickupLoot() : array{
		return $this->pickUpLoot;
	}

}
