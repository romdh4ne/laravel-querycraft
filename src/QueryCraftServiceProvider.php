<?php

namespace Romdh4ne\QueryCraft;

use Illuminate\Support\ServiceProvider;
use Romdh4ne\QueryCraft\Commands\AnalyzeCommand;
use Romdh4ne\QueryCraft\Services\QueryAnalysisService;

class QueryCraftServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                AnalyzeCommand::class,
            ]);

            // Publish config
            $this->publishes([
                __DIR__ . '/../config/querycraft.php' => config_path('querycraft.php'),
            ], 'querycraft-config');
        }

        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'querycraft');



        // Publish public assets (like icons)
        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/querycraft'),
        ], 'querycraft-assets');
    }

    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/querycraft.php',
            'querycraft'
        );

        $this->app->singleton(QueryAnalysisService::class, function ($app) {
            return new QueryAnalysisService();
        });
    }
}