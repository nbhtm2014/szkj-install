<?php
/**
 * Creator htm
 * Created by 2020/11/6 13:14
 **/

namespace Szkj\Install\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Szkj\Install\Seeder\ItemSeeder;
use Szkj\Install\Seeder\UserSeeder;
use Szkj\Install\Seeder\WechatSeeder;

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

        /**
         * create baseController
         */
        $this->createBaseController();

        /**
         * create baseRequest
         */
        $this->createBaseRequest();
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
            ["--provider" => "Szkj\Install\Providers\SzkjServiceProvider"]
        );
    }

    /**
     * @param $platforms
     */
    protected function seeder($platforms)
    {
        $this->call('db:seed', ["--class" => UserSeeder::class]);
        foreach ($platforms as $k => $v) {
            if (hash_equals($v, '电商平台')) {
                $this->call('db:seed', ['--class' => ItemSeeder::class]);
            }
            if (hash_equals($v, '微信公众号')) {
                $this->call('db:seed', ['--class' => WechatSeeder::class]);
            }
            if (hash_equals($v, '服务平台')) {

            }
        }
    }


    /**
     * Create HomeController.
     *
     * @return void
     */
    public function createBaseController()
    {
        $baseController = app_path('Http') . '/Controllers/BaseController.php';
        $contents = $this->getStub('Controllers/BaseController');

        $this->laravel['files']->put(
            $baseController,
            str_replace(
                'DummyNamespace',
                'App\\Http\\Controllers',
                $contents
            )
        );
        $this->line('<info>BaseController file was created:</info> ' . str_replace(base_path(), '', $baseController));
    }

    /**
     * Create BaseRequest.
     *
     * @return void
     */
    public function createBaseRequest()
    {
        if (!is_dir(app_path().'/Http/Requests')) {
            $this->makeDir('Http/Requests');
        }
        $baseRequest = app_path('Http') . '/Requests/BaseRequest.php';

        $contents = $this->getStub('Requests/BaseRequest');

        $this->laravel['files']->put(
            $baseRequest,
            str_replace(
                'DummyNamespace',
                'App\\Http\\Requests',
                $contents
            )
        );
        $this->line('<info>BaseRequest file was created:</info> ' . str_replace(base_path(), '', $baseRequest));
    }

    /**
     * Get stub contents.
     *
     * @param $name
     *
     * @return string
     */
    protected function getStub($name)
    {
        return $this->laravel['files']->get(__DIR__ . "/../../stubs/$name.stub");
    }

    /**
     * Make new directory.
     *
     * @param string $path
     */
    protected function makeDir($path = '')
    {
        $this->laravel['files']->makeDirectory(app_path().'/'.$path, 0755, true, true);
    }
}