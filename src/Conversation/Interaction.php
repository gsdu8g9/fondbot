<?php

declare(strict_types=1);

namespace FondBot\Conversation;

use FondBot\Traits\Loggable;
use FondBot\Contracts\Channels\User;
use Illuminate\Contracts\Bus\Dispatcher;
use FondBot\Conversation\Traits\Transitions;
use FondBot\Conversation\Commands\SendMessage;
use FondBot\Contracts\Channels\ReceivedMessage;
use FondBot\Contracts\Conversation\Interaction as InteractionContract;

abstract class Interaction implements InteractionContract
{
    use Transitions, Loggable;

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->getContext()->getUser();
    }

    /**
     * Get user's message.
     *
     * @return ReceivedMessage
     */
    public function getUserMessage(): ReceivedMessage
    {
        return $this->getContext()->getMessage();
    }

    /**
     * Do something before running Interaction.
     */
    protected function before(): void
    {
    }

    /**
     * Do something after running Interaction.
     */
    protected function after(): void
    {
    }

    /**
     * Run interaction.
     */
    public function run(): void
    {
        $this->debug('run');

        // Perform actions before running interaction
        $this->before();

        // Process reply if current interaction in context
        // Reply to participant if not
        if ($this->context->getInteraction() instanceof $this) {
            $this->debug('run.process');
            $this->process();

            // If no transition run we need to clear context.
            if (!$this->transitioned) {
                $this->clearContext();
            }

            $this->after();

            return;
        }

        // Set current interaction in context
        $this->context->setInteraction($this);

        // Send message to participant
        $this->getDispatcher()->dispatch(
            (new SendMessage(
                $this->getContext()->getChannel(),
                $this->getUser(),
                $this->text(),
                $this->keyboard()
            ))->onQueue('fondbot')
        );

        $this->updateContext();

        // Perform actions after running interaction
        $this->after();
    }

    /**
     * Process reply.
     */
    abstract protected function process(): void;

    private function getDispatcher(): Dispatcher
    {
        return resolve(Dispatcher::class);
    }
}
