<?php

declare(strict_types=1);

namespace VivaArcade\\World;

use Generator;
use pocketmine\\scheduler\\CancelTaskException;
use pocketmine\\scheduler\\Task;
use pocketmine\\world\\World;
use Throwable;

/**
 * Applies a lazy world-layout operation stream in bounded main-thread batches.
 *
 * PocketMine world APIs are only touched from this synchronous scheduler task.
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
        private readonly int $operationsPerTick = 240
    ){
        if($operationsPerTick < 1 || $operationsPerTick > 5000){
            throw new InvalidArgumentException('Invalid world build operations per tick');
        }
        $this->operations = $operations;
        $this->onComplete = $onComplete;
        $this->onFailure = $onFailure;
    }

    public function onRun() : void{
        try{
            for($processed = 0; $processed < $this->operationsPerTick; ++$processed){
                if(!$this->operations->valid()){
                    ($this->onComplete)();
                    throw new CancelTaskException();
                }

                $operation = $this->operations->current();
                $type = $operation['type'] ?? null;
                if($type === 'chunk'){
                    $this->world->loadChunk((int) $operation['x'], (int) $operation['z']);
                }elseif($type === 'block'){
                    $block = $operation['block'] ?? null;
                    if(!$block instanceof \\pocketmine\\block\\Block){
                        throw new \\UnexpectedValueException('World builder emitted an invalid block operation');
                    }
                    $this->world->setBlockAt(
                        (int) $operation['x'],
                        (int) $operation['y'],
                        (int) $operation['z'],
                        $block,
                        false
                    );
                }else{
                    throw new \\UnexpectedValueException('World builder emitted an unknown operation');
                }

                $this->operations->next();
            }
        }catch(CancelTaskException $e){
            throw $e;
        }catch(Throwable $e){
            ($this->onFailure)($e);
            throw new CancelTaskException();
        }
    }
}
