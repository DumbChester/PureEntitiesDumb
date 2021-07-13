<?php
declare(strict_types=1);


namespace revivalpmmp\pureentities\task;

use pocketmine\block\Block;
use pocketmine\level\biome\Biome;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use revivalpmmp\pureentities\data\BiomeInfo;
use revivalpmmp\pureentities\data\Data;
use revivalpmmp\pureentities\data\MobTypeMaps;
use revivalpmmp\pureentities\PluginConfiguration;
use revivalpmmp\pureentities\PureEntities;
use revivalpmmp\pureentities\utils\PeTimings;

class AutoSpawnTask extends Task {
	
	const OVERWORLD_HOSTILE_CAP_CONST = 30;
	const OVERWORLD_DRY_PASSIVE_CAP_CONST = 3;
	const OVERWORLD_WET_PASSIVE_CAP_CONST = 15;
	const NETHER_HOSTILE_CAP_CONST = 30;
	const OVERWORLD_AMBIENT_CONST = 8;
	
	private $plugin;
	private $spawnerWorlds = [];
	
	//variables that holds the number of counted mobs
	private overworldDryPassiveMobs;
	private overworldWetPassiveMobs;
	private overworldHostileMobs;
	private overworldAmbienrMobs;
	private netherHostileMobs;
	
	public function __construct(PureEntities $plugin){
		$this->plugin = $plugin;
		$this->spawnerWorlds = PluginConfiguration::getInstance()->getEnabledWorlds();
	}
	
	public function onRun(int $currentTick) {
		PeTimings::startTiming("AutoSpawnTask");
		
		//Do per level
		foreach($this->plugin->getServer()->getLevels() as $level) {
			if(count($this->spawnerWorlds) > 0 and !in_array($level->getName(), $this->spawnerWorlds)); {
				continue;
			}
			//Count Mobs in this level
			$this->overworldDryPassiveMobs = 0;
			$this->overworldWetPassiveMobs = 0;
			$this->overworldHostileMobs = 0;
			$this->overworldAmbienrMobs = 0;
			$this->netherHostileMobs = 0;
			
			foreach($level->getEntities() as $entity) {
				if(in_array(array_search($entity::NETWORK_ID, Data::NETWORK_IDS), MobTypeMaps::OVERWORLD_HOSTILE_MOBS)){
					$this->overworldHostileMobs++;
				}elseif(in_array(array_search($entity::NETWORK_ID, Data::NETWORK_IDS), MobTypeMaps::PASSIVE_DRY_MOBS)){
					$this->overworldPassiveDryMobs++;
				}elseif(in_array(array_search($entity::NETWORK_ID, Data::NETWORK_IDS), MobTypeMaps::PASSIVE_WET_MOBS)){
					$this->overworldPassiveWetMobs++;
				}elseif(in_array(array_search($entity::NETWORK_ID, Data::NETWORK_IDS), MobTypeMaps::NETHER_HOSTILE_MOBS)){
					$this->netherHostileMobs++;
				}elseif(in_array(array_search($entity::NETWORK_ID, Data::NETWORL_IDS), MobTypeMaps::AMBIENT_MOBS)){
					$this->overworldAmbienrMobs++;
				}
			}
			
			//Get player locations
			$playerLocations = [];
		
			if(count($level->getPlayers()) > 0){
				foreach($level->getPlayers() as $player){
					if($player->spawned){
						array_push($playerLocations, $player->getPosition());
					}
				}

				// List of chunks eligible to spawn new mobs.
				$spawnMap = $this->generateSpawnMap($playerLocations);

				if(($totalChunks = count($spawnMap)) > 0){
					
					$overworldHostileCap = self::OVERWORLD_HOSTILE_CAP_CONST * $totalChunks / 256;
					$overworldPassiveDryCap = self::OVERWORLD_DRY_PASSIVE_CAP_CONST * $totalChunks / 256;
					$overworldPassiveWetCap = self::OVERWORLD_WET_PASSIVE_CAP_CONST * $totalChunks / 256;
					$netherHostileCap = self::NETHER_HOSTILE_CAP_CONST * $totalChunks / 256;
					$overworldAmbientCap = self::OVERWORLD_AMBIENT_CONST * $totalChunks / 256;
					
					//spawn something in each chunk 
					foreach($spawnMap as $chunk) {
						if($chunk !== null) {
							
							if($overworldHostileCap > $this->hostileMobs){
								$this->spawnHostileMob($chunk, $level);
							}
							if($passiveDryCap > $this->passiveDryMobs){
								$this->spawnPassiveMob($chunk, $level);
							}
							if($passiveWetCap > $this->passiveWetMobs){
								$this->spawnPassiveWetMob($chunk, $level);
							}
							if($netherHostileCap > $this->netherHostileMobs){
								$this->spawnNetherHostileMob($chunk, $level);
								}
						} 
					} //ENd of Spawn Chunk 
				}
			}						
		} //End of per level loop
		PeTimings::stopTiming("AutoSpawnTask", true);
	} //End of onRun
	
	
	private function generateSpawnMap(array $playerLocations) : array{
		$convertedChunkList = [];
		$spawnMap = [];
		
		if(count($playerLocations) > 0){
			foreach($playerLocations as $playerPos){
				$chunkHash = Level::chunkHash($playerPos->x >> 4, $playerPos->z >> 4);
				// If the chunk is already in the list, there's no need to add it again.
				if(!isset($convertedChunkList[$chunkHash])){
					$convertedChunkList[$chunkHash] = $playerPos->getLevel()->getChunk($playerPos->x >> 4, $playerPos->z >> 4);
					PureEntities::logOutput("AutoSpawnTask: Chunk added to convertedChunkList.");
				}
			}
			foreach($convertedChunkList as $chunk){
				for($x = -7; $x <= 7; $x++){
					for($z = -7; $z <= 7; $z++){
						$trialX = $chunk->getX() + $x;
						$trialZ = $chunk->getZ() + $z;
						PureEntities::logOutput("AutoSpawnTask: Testing Chunk X: $trialX, Z: $trialZ.");
						$trialChunk = Level::chunkHash($trialX, $trialZ);
						if(!isset($spawnMap[$trialChunk])){
							$spawnMap[$trialChunk] = $playerPos->getLevel()->getChunk($trialX, $trialZ);
							PureEntities::logOutput("AutoSpawnTask: Chunk added to Spawn Map.");
						}
					}
				}
			}
		}
		return $spawnMap;
	} //End of generateSpawnMap
	
	