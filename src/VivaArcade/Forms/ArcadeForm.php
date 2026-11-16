<?php

declare(strict_types=1);

namespace VivaArcade\Forms;

use Closure;
use JsonSerializable;
use pocketmine\form\Form;
use pocketmine\player\Player;

/** Native Bedrock JSON form without external FormAPI dependencies. */
final class ArcadeForm implements Form, JsonSerializable{

    /** @var list<string> */
    private array $buttons;
    private Closure $handler;

    /**
     * @param list<string> $buttons
     * @param callable(Player, int): void $handler
     */
    public function __construct(
        private readonly string $title,
        private readonly string $content,
        array $buttons,
        callable $handler
    ){
        $this->buttons = array_values(array_map(
            static fn(mixed $button) : string => substr((string) $button, 0, 48),
            $buttons
        ));
        $this->handler = Closure::fromCallable($handler);
    }

    public function jsonSerialize() : array{
        return [
            "type" => "form",
            "title" => $this->title,
            "content" => $this->content,
            "buttons" => array_map(
                static fn(string $button) : array => ["text" => $button],
                $this->buttons
            )
        ];
    }

    /** PocketMine-MP Form interface accepts untyped response data from clients. */
    public function handleResponse(Player $player, $data) : void{
        if(!is_int($data) || !array_key_exists($data, $this->buttons)){
            return;
        }
        ($this->handler)($player, $data);
    }
}
