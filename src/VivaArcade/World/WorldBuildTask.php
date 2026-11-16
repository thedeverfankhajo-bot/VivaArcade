<?php

declare(strict_types=1);

namespace VivaArcade\World;

use Generator;
use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\world\World;
use Throwable;

/**
 * Applies a lazy world-layout operation stream in bounded main-thread batches.
 * Chunk generation is limited separately because it is more expensive than
 * writing blocks. No PocketMine world object is touched by an async worker.
 */
final class WorldBuildTask extends Task{

    /** @var Generator<int, array<string, mixed>, mixed, void> */
    private Generator $operations;

    /** @var callable(): void */
    private $onComplete;

    /** @var callable(Throwable): void */
    private $onFailure;

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
        private readonly int $blocksPerTick = 180,
        private readonly int $chunksPerTick = 1
    ){
        if($blocksPerTick < 1 || $blocksPerTick > 5000 || $chunksPerTick < 1 || $chunksPerTick > 8){
            throw new InvalidArgumentException('Invalid world build task budget');
        }
        $this->operations = $operations;
        $this->onComplete = $onComplete;
        $this->onFailure = $onFailure;
    }

    public function onRun() : void{
        $blocks = 0;
        $chunks = 0;

        try{
            while($this->operations->valid()){
                $operation = $this->operations->current();
                $type = $operation['type'] ?? null;

                if($type === 'chunk'){
                    if($chunks >= $this->chunksPerTick){
                        return;
                    }
                    $this->world->loadChunk((int) $operation['x'], (int) $operation['z']);
                    ++$chunks;
                }elseif($type === 'block'){
                    if($blocks >= $this->blocksPerTick){
                        return;
                    }
                    $block = $operation['block'] ?? null;
                    if(!$block instanceof Block){
                        throw new \UnexpectedValueException('World builder emitted an invalid block operation');
                    }
                    $this->world->setBlockAt(
                        (int) $operation['x'],
                        (int) $operation['y'],
                        (int) $operation['z'],
                        $block,
                        false
                    );
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

            ($this->onComplete)();
        }catch(CancelTaskException $e){
            throw $e;
        }catch(Throwable $e){
            ($this->onFailure)($e);
        }

        throw new CancelTaskException();
    }
}
