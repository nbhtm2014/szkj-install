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
        /**
         * publish config file
         */
        $this->publishConfig();
        /**
         * seed database
         */
        $this->seeder();
        /**
         * create baseController
         */
        $this->createBaseController();
        /**
         * create baseRequest
         */
        $this->createBaseRequest();

        if ($this->confirm('Do you need to release the RBAC file?')) {
            $this->call('szkj:rbac-install');
        }
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


    protected function seeder()
    {
        $this->call('db:seed', ["--class" => UserSeeder::class]);
        $this->call('db:seed', ['--class' => ItemSeeder::class]);
        $this->call('db:seed', ['--class' => WechatSeeder::class]);
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