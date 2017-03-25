<?php

declare(strict_types=1);

namespace Tests;

use Faker\Factory as Faker;
use Faker\Generator;
use FondBot\Contracts\Channels\Message\Attachment;
use FondBot\Contracts\Channels\Message\Location;
use FondBot\Contracts\Channels\Sender;
use FondBot\Contracts\Channels\SenderMessage;
use Illuminate\Database\Eloquent\Model;
use Tests\Classes\Fakes\FakeDriver;
use FondBot\Contracts\Database\Entities\Channel;
use FondBot\Contracts\Database\Entities\Participant;
use Tests\Classes\Fakes\FakeSenderMessage;

class Factory
{
    /** @var string */
    protected $class;

    public function __construct(string $class = null)
    {
        $this->class = $class;
    }

    protected function factories(): array
    {
        return [
            Channel::class => [
                'driver' => FakeDriver::class,
                'name' => $this->faker()->word,
                'parameters' => ['token' => str_random()],
            ],
            Participant::class => [
                'channel_id' => $this->faker()->numberBetween(),
                'identifier' => $this->faker()->uuid,
                'name' => $this->faker()->name,
                'username' => $this->faker()->userName,
            ],
        ];
    }

    /**
     * @param array $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    public function create(array $attributes = []): Model
    {
        $attributes = array_merge($this->factories()[$this->class], $attributes);

        return new $this->class($attributes);
    }

    /**
     * @param array $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    public function save(array $attributes = []): Model
    {
        $instance = $this->create($attributes);
        $instance->save();

        return $instance->fresh();
    }

    public function sender(array $attributes = []): Sender
    {
        return Sender::create(
            $attributes['uuid'] ?? $this->faker()->uuid,
            $attributes['name'] ?? $this->faker()->name,
            $attributes['username'] ?? $this->faker()->userName
        );
    }

    public function senderMessage(array $attributes = []): SenderMessage
    {
        $location = array_has($attributes,
            'location') ? $attributes['location'] : new Location($this->faker()->latitude,
            $this->faker()->longitude);
        $attachment = array_has($attributes, 'attachment') ? $attributes['attachment'] : new Attachment('image',
            $this->faker()->imageUrl());

        return new FakeSenderMessage(
            $attributes['text'] ?? $this->faker()->text,
            $location,
            $attachment
        );
    }

    private function faker(): Generator
    {
        return Faker::create();
    }
}