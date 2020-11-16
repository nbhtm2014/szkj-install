<?php
/**
 * Creator htm
 * Created by 2020/11/16 15:01
 **/

namespace Szkj\Install\Console\Commands;


use Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;

class InitDataCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'szkj:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init Data';


    /**
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $task = 1;

    /**
     * @var string
     */
    protected $scroll = '2m';

    /**
     * @return string
     */
    public function getConnection(): string
    {
        return config('database.default');
    }

    public function __construct()
    {
        parent::__construct();
        $this->client = ClientBuilder::create()->setHosts(config('szkj.elasticsearch-hosts'))->build();
    }


    public function handle()
    {
        $params = $this->createParam();
        dd($params);
    }

    protected function createParam(): array
    {
        $terms = $this->getPCD();
        return [
            'index'  => 'commodity-info',
            'scroll' => $this->scroll,
            'body'   => [
                "query" => [
                    "bool" => [
                        "must" => $terms
                    ],
                ],
            ],
        ];
    }

    protected function getPCD(array $array = []): array
    {
        $pcd = config('szkj.pcd');
        foreach ($pcd as $k => $v) {
            if (!empty($v) && count($v)) {
                $array[] = [
                    'term' => [
                        "firmInfo.registrationAuthority.{$k}.keyword" => [
                            'value'=>$v
                        ]
                    ]
                ];
            }
        }
        return $array;
    }
}