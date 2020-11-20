<?php
/**
 * Creator htm
 * Created by 2020/11/6 13:30
 **/

namespace Szkj\Install\Providers;


use Illuminate\Support\ServiceProvider;
use Szkj\Install\Console\Commands\InitAreasCommand;
use Szkj\Install\Console\Commands\InitDataCommand;
use Szkj\Install\Console\Commands\InstallCommand;

class SzkjServiceProvider extends ServiceProvider
{

    /**
     * @var array
     */
    protected $commands = [
        InstallCommand::class,
        InitDataCommand::class,
        InitAreasCommand::class
    ];
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->registerMigrations();
        $this->registerPublishing();
    }

    public function register()
    {
        $this->commands($this->commands);
    }

    /**
     *
     * @return void
     */
    protected function registerPublishing()
    {
        $this->publishes([realpath(__DIR__.'/../../config/szkj.php') => config_path('szkj.php')]);
    }

    /**
     * 表迁移
     */
    public function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}