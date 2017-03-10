<?php declare(strict_types=1);

namespace FondBot\Conversation\Keyboards;

class Button
{

    /** @var string */
    private $value;

    public static function create(string $value): Button
    {
        $instance = new static;
        $instance->setValue($value);

        return $instance;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

}