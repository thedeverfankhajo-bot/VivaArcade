<?php

declare(strict_types=1);

namespace VivaArcade\World;

use Generator;
use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\promise\Promise;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\world\World;
use Throwable;

/**
 * Applies a lazy world-layout operation stream in bounded main-thread batches.
 *
 * Chunk terrain is generated asynchronously through World::orderChunkPopulation().
 * The task pauses until the promise resolves before writing blocks into that chunk.
 * World/block mutations remain on PocketMine's main thread.
 */
final class WorldBuildTask extends Task{

    /** @var Generator<int, array<string, mixed>, mixed, void> */
    private Generator $operations;

    /** @var callable(): void */
    private $onComplete;

    /** @var callable(Throwable): void */
    private $onFailure;

    private bool $waitingForChunk = false;
    private ?Throwable $chunkFailure = null;
    private bool $finished = false;

    /**
     * @param Generator<int, array<string, mixed>, mixed, void> $operations
     * @param callable(): void $onComplete
     * @param callable(Throwable): void $onFailure
     */
    public function __construct(
        private readonly World $world,
        Generator $operations,
        callable $onComplete,
        callable $onFailure,
        private readonly int $blocksPerTick = 180
    ){
        if($blocksPerTick < 1 || $blocksPerTick > 5000){
            throw new InvalidArgumentException('Invalid world build block budget');
        }
        $this->operations = $operations;
        $this->onComplete = $onComplete;
        $this->onFailure = $onFailure;
    }

    public function onRun() : void{
        if($this->finished){
            throw new CancelTaskException();
        }

        if($this->chunkFailure !== null){
            $this->finished = true;
            ($this->onFailure)($this->chunkFailure);
            throw new CancelTaskException();
        }

        if($this->waitingForChunk){
            return;
        }

        try{
            $blocks = 0;
            while($this->operations->valid()){
                $operation = $this->operations->current();
                $type = $operation['type'] ?? null;

                if($type === 'chunk'){
                    // Loading a chunk does not imply terrain generation. Wait for
                    // the population promise before any block operation can run.
                    $this->waitingForChunk = true;
                    $chunkX = (int) $operation['x'];
                    $chunkZ = (int) $operation['z'];

                    $promise = $this->world->orderChunkPopulation($chunkX, $chunkZ, null);
                    $promise->onCompletion(
                        function() : void{
                            if(!$this->finished){
                                $this->operations->next();
                                $this->waitingForChunk = false;
                            }
                        },
                        function() : void{
                            if(!$this->finished){
                                $this->chunkFailure = new \RuntimeException('PocketMine failed to generate a required VivaArcade chunk');
                                $this->waitingForChunk = false;
                            }
                        }
                    );
                    return;
                }

                if($type === 'block'){
                    if($blocks >= $this->blocksPerTick){
                        return;
                    }

                    $x = (int) $operation['x'];
                    $y = (int) $operation['y'];
                    $z = (int) $operation['z'];
                    if(!$this->world->isChunkGenerated($x >> 4, $z >> 4)){
                        throw new \UnexpectedValueException('World builder attempted to edit a chunk before terrain generation');
                    }

                    $block = $operation['block'] ?? null;
                    if(!$block instanceof Block){
                        throw new \UnexpectedValueException('World builder emitted an invalid block operation');
                    }
                    $this->world->setBlockAt($x, $y, $z, $block, false);
                    ++$blocks;
                }elseif($type === 'spawn'){
                    $this->world->setSpawnLocation(new Vector3(
                        (float) $operation['x'],
                        (float) $operation['y'],
                        (float) $operation['z']
                    ));
                }else{
                    throw new \UnexpectedValueException('World builder emitted an unknown operation');
                }

                $this->operations->next();
            }

            $this->finished = true;
            ($this->onComplete)();
        }catch(CancelTaskException $e){
            throw $e;
        }catch(Throwable $e){
            $this->finished = true;
            ($this->onFailure)($e);
        }

        throw new CancelTaskException();
    }
}
