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
use pocketmine\level\Level;
use revivalpmmp\pureentities\data\Data;
use revivalpmmp\pureentities\entity\monster\Monster;
use revivalpmmp\pureentities\entity\monster\WalkingMonster;
use revivalpmmp\pureentities\PureEntities;
use revivalpmmp\pureentities\utils\MobDamageCalculator;

//use pocketmine\event\Timings;

class Witch extends WalkingMonster implements Monster{

	// Witch class was based from Zombie class.
	// TODO Update methods to be specific to Witches instead of Zombies.

	const NETWORK_ID = Data::NETWORK_IDS["witch"];

	public function initEntity() : void{
		parent::initEntity();
		$this->speed = 1.1;
		$this->setDamage([0, 2, 3, 4]);
	}

	/**
	 * Returns the appropriate NetworkID associated with this entity
	 * @return int
	 */
	public function getNetworkId(){
		return self::NETWORK_ID;
	}

	public function getName() : string{
		return "Witch";
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
	 * Witch gets attacked. We need to recalculate the damage done with reducing the damage by armor type.
	 *
	 * @param EntityDamageEvent $source
	 */
	public function attack(EntityDamageEvent $source) : void{
		$damage = $this->getDamage();
		PureEntities::logOutput("$this: attacked with original damage of $damage");
		$reduceDamagePercent = 0;
		if($reduceDamagePercent > 0){
			$reduceBy = $damage * $reduceDamagePercent / 100;
			PureEntities::logOutput("$this: reduce damage by $reduceBy");
			$damage = $damage - $reduceBy;
		}

		PureEntities::logOutput("$this: attacked with final damage of $damage");

		parent::attack($source);
	}

	/**
	 * This witch attacks a player
	 *
	 * @param Entity $player
	 */
	public function attackEntity(Entity $player){
		if($this->attackDelay > 10 && $this->distanceSquared($player) < 2){
			$this->attackDelay = 0;
			// maybe this needs some rework ... as it should be calculated within the event class and take
			// mob's weapon into account. for now, i just add the damage from the weapon the mob wears
			$damage = $this->getDamage();
			$ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK,
				MobDamageCalculator::calculateFinalDamage($player, $damage));
			$player->attack($ev);

			$this->checkTamedMobsAttack($player);
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->isClosed()) return false;
		// Timings::$timerEntityBaseTick->startTiming();

		$hasUpdate = parent::entityBaseTick($tickDiff);

		$time = $this->getLevel() !== null ? $this->getLevel()->getTime() % Level::TIME_FULL : Level::TIME_NIGHT;
		if(
			!$this->isOnFire()
			&& ($time < Level::TIME_NIGHT || $time > Level::TIME_SUNRISE)
		){
			$this->setOnFire(100);
		}
		// Timings::$timerEntityBaseTick->stopTiming();
		return $hasUpdate;
	}

	public function getDrops() : array{
		$drops = [];
		if($this->isLootDropAllowed()){
			// 2.5 percent chance of dropping one of these items.
			if(mt_rand(1, 1000) % 25 === 0){
				switch(mt_rand(1, 3)){
					case 1:
						array_push($drops, Item::get(Item::GLASS_BOTTLE, 0, 1));
						break;
					case 2:
						array_push($drops, Item::get(Item::GLOWSTONE_DUST, 0, 1));
						break;
					case 3:
						array_push($drops, Item::get(Item::GUNPOWDER, 0, 1));
						break;
					case 4:
						array_push($drops, Item::get(Item::REDSTONE, 0, 1));
						break;
					case 5:
						array_push($drops, Item::get(Item::SPIDER_EYE, 0, 1));
						break;
					case 6:
						array_push($drops, Item::get(Item::SUGAR, 0, 1));
						break;
					case 7:
						array_push($drops, Item::get(Item::STICK, 0, 1));
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
		$this->xpDropAmount = 5;
	}

}
