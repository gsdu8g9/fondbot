<?php

declare(strict_types=1);

namespace Tests\Unit\Channels;

use FondBot\Channels\Message;
use Tests\TestCase;

class MessageTest extends TestCase
{
    public function test_create()
    {
        $text = 'Hello user!';

        $message = Message::create($text);

        $this->assertEquals($text, $message->getText());
    }
}