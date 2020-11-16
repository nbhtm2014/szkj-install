<?php
/**
 * Creator htm
 * Created by 2020/11/16 15:01
 **/

namespace Szkj\Install\Console\Commands;


use Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
    protected $task_id = 1;

    /**
     * @var string
     */
    protected $scroll = '2m';

    /**
     * @var string
     */
    protected $scroll_id;

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

        $rep = $this->client->search($params);

        $this->initData($rep);

    }

    protected function initData(array $rep)
    {
        $this->scroll_id = $rep['_scroll_id'];
        try {
            if (count($rep['hits']['hits']) > 0) {
                while (true) {
                    $data = $this->client->scroll([
                        'scroll_id' => $this->scroll_id,
                        "scroll"    => $this->scroll,
                    ]);
                    //插入数据
                    $this->createData($data['hits']['hits']);
                    if (count($data['hits']['hits']) == 0) {
                        break;
                    }
                    $this->scroll_id = $data['_scroll_id'];
                }
            }
            $this->clear();
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
            $this->clear();
        }
    }

    protected function createData(array $data)
    {
        foreach ($data as $k => $v) {
            $this->createItem($v['_source']);
            $this->createShop($v['_source']);
            $this->createEntity($v['_source']);
        }
    }


    protected function createItem(array $array)
    {
        $table_name = 'items_' . $this->task_id;
        $this->createItemsTable($table_name);
        if (!DB::connection($this->getConnection())
            ->table($table_name)
            ->where('item_platform', $array['platform'])
            ->where('nid', $array['commodityId'])
            ->count()) {
            $data['task_id'] = $this->task_id;
            if (isset($array['keyword']) && !empty($array['keyword'])) {
                $data['keyword'] = $array['keyword'];
            }
            $data['nid'] = $array['commodityId'];
            $data['category_id'] = $array['categoryId'];
            $data['title'] = $array['title'];
            $data['location'] = $array['location'];
            $data['shop_id'] = $array['shopId'];
            $data['platform_id'] = $array['platform'];
            if (isset($array['nickname']) && !empty($array['nickname'])) {
                $data['nick'] = $array['nickname'];
            }
            if (isset($array['shopNickname']) && !empty($array['shopNickname'])) {
                $data['nick'] = $array['shopNickname'];
            }
            if (isset($array['shopSellerId']) && !empty($array['shopSellerId'])) {
                $data['seller_id'] = $array['shopSellerId'];
            }
            if (isset($array['categoryLabel']) && !empty($array['categoryLabel'])) {
                $data['classify'] = $array['categoryLabel'];
            }
            if (is_array($array['properties'])) {
                $data['property'] = json_encode($array['properties']);
            } elseif ($property = json_decode($array['properties'], false)) {
                if (($property && is_object($property)) || (is_array($property) && !empty($property))) {
                    $data['property'] = $array['properties'];
                }
            }
            $data['item_url'] = $array['url'];
            $data['comment_count'] = $array['commentCountF'];
            $data['view_price'] = $array['viewPrice'];
            $data['view_sales'] = $array['viewSalesVolumeF'];
            $data['view_amount'] = round($data['view_price'] * $data['view_sales']);
            $data['created_at'] = now();
            $data['updated_at'] = now();
            DB::connection($this->getConnection())->table($table_name)->insert($data);
        }
    }

    protected function createShop(array $array)
    {

    }

    protected function createEntity(array $array)
    {

    }


    protected function createItemsTable($table_name)
    {
        if (!Schema::hasTable($table_name)) {
            Schema::create($table_name, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('task_id')->default(0)->comment('任务id');
                $table->string('batch', 50)->nullable()->comment('批号');
                $table->string('keyword', 50)->nullable()->comment('关键词');
                $table->string('nid', 50)->comment('商品id');
                $table->string('category_id', 200)->nullable()->comment('原始分类id');
                $table->string('title', 500)->comment('标题');
                $table->string('location', 50)->nullable()->comment('发货地');
                $table->string('shop_id', 50)->comment('店铺id');
                $table->double('view_price')->nullable()->comment('列表价格');
                $table->integer('view_sales')->nullable()->comment('显示销量');
                $table->integer('comment_count')->nullable()->comment('评论数量');
                $table->integer('platform_id')->comment('平台');
                $table->json('property')->nullable()->comment('属性');
                $table->double('view_amount')->nullable()->comment('总销售额');
                $table->string('nick', 255)->nullable();
                $table->string('seller_id', 255)->nullable();
                $table->string('classify', 255)->nullable()->comment('公司分类');
                $table->string('item_url', 255)->nullable()->comment('商品链接');
                $table->timestamps();
            });
        }
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
                        "must" => $terms,
                    ],
                ],
            ],
        ];
    }

    protected function getPCD(array $array = []): array
    {
        $pcd = config('szkj.pcd');
        foreach ($pcd as $k => $v) {
            if (!empty($v)) {
                $array[] = [
                    'term' => [
                        "firmInfo.registrationAuthority.{$k}.keyword" => [
                            'value' => $v,
                        ],
                    ],
                ];
            }
        }
        return array_values($array);
    }


    protected function clear()
    {
        $this->client->clearScroll([
            'scroll_id' => $this->scroll_id,
        ]);
    }
}