<?php

namespace Yuyu\FileManager\Providers;

use Illuminate\Support\ServiceProvider;

class FileManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register Middleware
        app('router')->aliasMiddleware('StorageAccessValidator', \Yuyu\FileManager\Middleware\StorageAccessValidator::class);

        // Register resources and console commands if app is running in console.
        if ($this->app->runningInConsole()) {
            $this->registerPublishableResources();
            $this->registerConsoleCommands();
        }

        // Register fileManager Service
        $this->app->bind('fileManager', function(){
            return new \Yuyu\FileManager\Controllers\FileManagerController;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Routes using scopes
        include __DIR__.'/../../routes/fileManager.php';
    }

    /**
     * Register the commands accessible from the Console.
     */
    private function registerConsoleCommands()
    {
        // Console Commands
    }

    /**
     * Register the publishable files.
     */
    private function registerPublishableResources()
    {
        $publishablePath = __DIR__.'/../../publishable';

        $publishable = [
            'migration' => [
                "{$publishablePath}/database/migrations/" => database_path('migrations'),
            ],
            'seeds' => [
                "{$publishablePath}/database/seeds/" => database_path('seeds'),
            ],
            'models' => [
                "{$publishablePath}/Models" => app_path('Models'),
            ]
        ];

        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }
}
