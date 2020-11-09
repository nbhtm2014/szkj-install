<?php
/**
 * Creator htm
 * Created by 2020/11/6 13:14
 **/

namespace Szkj\Install\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'szkj:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the collection package';

    /**
     * Install directory.
     *
     * @var string
     */
    protected $directory = '';


    /**
     * @return string
     */
    public function getConnection(): string
    {
        return config('database.default');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $log = <<<ETO
        ███████╗     ███████╗    ██╗  ██╗         ██╗
        ██╔════╝     ╚══███╔╝    ██║ ██╔╝         ██║
        ███████╗     ███╔╝       █████╔╝          ██║
        ╚════██║     ███╔╝       ██╔═██╗     ██   ██║
        ███████║     ███████╗    ██║  ██╗    ╚█████╔╝
        ╚══════╝     ╚══════╝    ╚═╝  ╚═╝     ╚════╝
ETO;
        $this->info($log);
        if (!Schema::hasTable('platforms')) {
            $this->call('migrate');
        }
        $this->publishConfig();

        $platforms = $this->choice('请选择采集平台（多选请用逗号隔开,比如0,1,2）',
            ['电商平台', '微信公众号', '服务平台'],
            0,
            null,
            true);
        $this->seeder($platforms);
    }

    /**
     * push config file
     */
    protected function publishConfig()
    {
        $this->call('vendor:publish',
            ["--provider" => "Dingo\Api\Provider\LaravelServiceProvider"]
        );
        $this->call('vendor:publish',
            ["--provider" => "Szkj\Install\Provider\SzkjServiceProvider"]
        );
    }

    /**
     * @param $platforms
     */
    protected function seeder($platforms)
    {
        $this->call('db:seed', ["--class" => "UserSeeder"]);
        foreach ($platforms as $k => $v) {
            if (hash_equals($v, '电商平台')) {
                $this->call('db:seed', ['--class' => 'ItemSeeder']);
            }
            if (hash_equals($v, '微信公众号')) {
                $this->call('db:seed', ['--class' => 'WechatSeeder']);
            }
            if (hash_equals($v, '服务平台')) {

            }
        }
    }
}