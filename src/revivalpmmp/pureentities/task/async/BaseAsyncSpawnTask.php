<?php
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

declare(strict_types=1);

namespace revivalpmmp\pureentities\task\async;


use pocketmine\level\format\Chunk;
use pocketmine\scheduler\AsyncTask;

abstract class BaseAsyncSpawnTask extends AsyncTask{


	/** @var Chunk[] */
	protected $chunks;

	protected $currentMobCounts;

	protected $mobCaps;

	public function __construct(array $chunks, array $mobCaps = [], array $currentMobCounts = []){
		$this->chunks = $chunks;
	}

}