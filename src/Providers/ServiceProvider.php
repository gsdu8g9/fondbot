<?php

declare(strict_types=1);

namespace FondBot\Providers;

use Route;
use FondBot\Contracts\Database\Entities\Channel;
use FondBot\Contracts\Database\Services\ChannelService;
use FondBot\Contracts\Database\Services\MessageService;
use FondBot\Contracts\Providers\ChannelServiceProvider;
use FondBot\Contracts\Database\Services\ParticipantService;
use FondBot\Contracts\Providers\ConversationServiceProvider;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->register(ChannelServiceProvider::class);
        $this->app->register(ConversationServiceProvider::class);
        $this->contracts();
        $this->console();
    }

    public function boot()
    {
        $this->routes();
    }

    /**
     * Register contracts.
     */
    private function contracts(): void
    {
        $this->app->bind(ChannelService::class, \FondBot\Database\Services\ChannelService::class);
        $this->app->bind(ParticipantService::class, \FondBot\Database\Services\ParticipantService::class);
        $this->app->bind(MessageService::class, \FondBot\Database\Services\MessageService::class);
    }

    /**
     * Register routes.
     */
    private function routes(): void
    {
        Route::model('channel', Channel::class);

        if (!$this->app->routesAreCached()) {
            Route::group([
                'prefix' => 'fondbot',
                'namespace' => 'FondBot\Http\Controllers',
                'middleware' => ['bindings'],
            ], function () {
                require __DIR__.'/../../resources/routes/channels.php';
            });
        }
    }

    /**
     * Register console commands.
     */
    private function console(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../../resources/migrations');
            $this->publishes([
                __DIR__.'/../../resources/config/fondbot.php' => config_path('fondbot.php'),
            ], 'fondbot');

            $this->commands([
                \FondBot\Console\CreateChannel::class,
                \FondBot\Console\DeleteChannel::class,
                \FondBot\Console\EnableChannel::class,
                \FondBot\Console\DisableChannel::class,
                \FondBot\Console\ListChannels::class,
                \FondBot\Console\CreateStory::class,
                \FondBot\Console\CreateInteraction::class,
                \FondBot\Console\WebhookInstall::class,
                \FondBot\Console\Install::class,
                \FondBot\Console\Update::class,
            ]);
        }
    }
}
