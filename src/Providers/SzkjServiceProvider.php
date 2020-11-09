<?php
/**
 * Creator htm
 * Created by 2020/11/6 13:30
 **/

namespace Szkj\Install\Providers;


use Illuminate\Support\ServiceProvider;
use Szkj\Install\Console\Commands\InstallCommand;

class SzkjServiceProvider extends ServiceProvider
{

    /**
     * @var array
     */
    protected $commands = [
        InstallCommand::class,
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
        $this->publishes([__DIR__ . '/../../config' => config_path()], 'szkj-config');
    }

    /**
     * 表迁移
     */
    public function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}