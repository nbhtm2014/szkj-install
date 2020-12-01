<?php
/**
 * Creator htm
 * Created by 2020/11/16 15:01
 **/

namespace Szkj\Install\Console\Commands;


use Elasticsearch\Client;
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
     * @var Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $task_id = 1;

    /**
     * @var string
     */
    protected $scroll = '1m';

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
        if (!empty(config('szkj.elasticsearch-hosts'))) {
            $this->client = ClientBuilder::create()->setHosts(config('szkj.elasticsearch-hosts'))->build();
        }
    }


    public function handle()
    {
        $this->initAreas();

        $params = $this->createParam();

        $rep = $this->client->search($params);

        $this->initData($rep);

    }

    protected function initAreas(){

        $areas = config('szkj.pcd');
        foreach ($areas as $k => $v){
            if(empty($v)){
                unset($areas);
            }
        }
        try {
            $key = array_key_last($areas);
            $pid = DB::connection($this->getConnection())
                ->table('ares')
                ->where('tag', $key)
                ->where('name', $areas[$key])
                ->first()
                ->id;
            DB::connection($this->getConnection())
                ->table('ares')->where('pid', '!=', $pid)->delete();
        }catch (\Exception $exception){
            $this->warn($exception->getMessage());
        }
    }

    protected function initData(array $rep)
    {
        //写入初始任务
        $this->insertTask();
        $this->scroll_id = $rep['_scroll_id'];
        try {
            if (count($rep['hits']['hits']) > 0) {
                //更新任务状态
                $this->updateTask('status',2);
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
                //更新任务状态
                $this->updateTask('status',4);
                $this->clear();
            }
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
            $this->clear();
        }
    }

    protected function insertTask()
    {
        if (!DB::connection($this->getConnection())->table('tasks')->where('id',$this->task_id)->count()) {
            $platforms = DB::connection($this->getConnection())->table('platforms')->pluck('id')->toArray();
            DB::connection($this->getConnection())->table('tasks')->insert([
                'id'          => $this->task_id,
                'title'       => '全网检测任务首次排查',
                'platform_id' => implode(',', $platforms),
                'status'      => 1,
                'user_id'     => DB::connection($this->getConnection())->table('users')->where('superadmin', 1)->first()->id,
                'pcd'         => trim(config('pcd.province') . ',' . config('pcd.city') . ',' . config('pcd.district'), ','),
                'type'        => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    /**
     * @param string $key
     * @param $value
     */
    protected function updateTask(string $key,$value){
        DB::connection($this->getConnection())
            ->table('tasks')
            ->where('id',$this->task_id)
            ->update(
            [$key=>$value]
        );
    }

    /**
     * @param array $data
     */
    protected function createData(array $data)
    {
        foreach ($data as $k => $v) {
            $this->createItem($v['_source']);
            $this->createShop($v['_source']);
            $this->createEntity($v['_source']);
        }
    }


    /**
     * @param array $array
     */
    protected function createItem(array $array)
    {
        $table_name = 'items_' . $this->task_id;
        $this->createItemsTable($table_name);
        if (!DB::connection($this->getConnection())
            ->table($table_name)
            ->where('platform_id', $array['platform'])
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
            $this->info('insert items success nid is ' . $data['nid']);
        }
    }

    /**
     * @param array $array
     */
    protected function createShop(array $array)
    {
        if (!DB::connection($this->getConnection())->table('shops')
            ->where('platform_id', $array['platform'])
            ->where('shop_id', $array['shopId'])
            ->first()) {
            $data['platform_id'] = $array['platform'];
            $data['shop_id'] = $array['shopId'];
            $data['name'] = $array['shopName'];
            if (isset($array['shopInfo']['nickname']) && !empty($array['shopInfo']['nickname'])) {
                $data['nick'] = $array['shopInfo']['nickname'];
            }
            if (isset($array['shopNickname']) && !empty($array['shopNickname'])) {
                $data['nick'] = $array['shopNickname'];
            }
            $data['shop_url'] = $array['shopUrl'];
            if (isset($array['shopInfo']['licenseUrl']) && !empty($array['shopInfo']['licenseUrl'])) {
                $data['licence_url'] = $array['shopInfo']['licenseUrl'];
            }
            //permit_url
            if (isset($array['shopInfo']['permitUrl']) && !empty($array['shopInfo']['permitUrl'])) {
                $data['permit_url'] = $array['shopInfo']['permitUrl'];
            }
            if (isset($array['firmInfo']['unifiedSocialCreditCode']) && !empty($array['firmInfo']['unifiedSocialCreditCode'])) {
                $data['credit_code'] = $array['firmInfo']['unifiedSocialCreditCode'];
            }
            if (isset($array['shopSellerId']) && !empty($array['shopSellerId'])) {
                $data['seller_id'] = $array['shopSellerId'];
            }
            if (isset($array['shopInfo']['sellerId']) && !empty($array['shopInfo']['sellerId'])) {
                $data['seller_id'] = $array['shopInfo']['sellerId'];
            }
            if (isset($array['firmInfo']['name']) && !empty($array['firmInfo']['name'])) {
                $data['company'] = $array['firmInfo']['name'];
            }
            if (isset($array['shopInfo']['memberId']) && !empty($array['shopInfo']['memberId'])) {
                $data['member_id'] = $array['shopInfo']['memberId'];
            }
            if (isset($array['shopInfo']['userId']) && !empty($array['shopInfo']['userId'])) {
                $data['item_user_id'] = $array['shopInfo']['userId'];
            }
            if (isset($array['shopInfo']['shopName']) && !empty($array['shopInfo']['shopName'])) {
                $data['name'] = $array['shopInfo']['shopName'];
            }
            $data['created_at'] = now();
            $data['updated_at'] = now();
            DB::connection($this->getConnection())->table('shops')->insert($data);
            $this->info('shop insert success shop_id is ' . $data['shop_id']);
        }
    }

    /**
     * @param array $array
     */
    protected function createEntity(array $array)
    {
        if (isset($array['firmInfo']['name']) && !empty($array['firmInfo'])) {
            if (!DB::connection()->table('entities')
                ->where('name', $array['firmInfo']['name'])
                ->first()
            ) {
                $create['name'] = $array['firmInfo']['name'];
                $create['credit_no'] = isset($array['firmInfo']['unifiedSocialCreditCode']) ? $array['firmInfo']['unifiedSocialCreditCode'] : null;
                $create['regist_no'] = isset($array['firmInfo']['registrationNo']) ? $array['firmInfo']['registrationNo'] : null;
                $create['address'] = isset($array['firmInfo']['businessAddress']) ? $array['firmInfo']['businessAddress'] : null;
                $create['frdb'] = isset($array['firmInfo']['legalLeader']) ? $array['firmInfo']['legalLeader'] : null;
                $create['djjg'] = isset($array['firmInfo']['registrationAuthority']['address']) ? $array['firmInfo']['registrationAuthority']['address'] : null;
                $create['province'] = isset($array['firmInfo']['registrationAuthority']['province']) ? $array['firmInfo']['registrationAuthority']['province'] : null;
                $create['city'] = isset($array['firmInfo']['registrationAuthority']['city']) ? $array['firmInfo']['registrationAuthority']['city'] : null;
                $create['district'] = isset($array['firmInfo']['registrationAuthority']['district']) ? $array['firmInfo']['registrationAuthority']['district'] : null;
                $create['town'] = isset($array['firmInfo']['registrationAuthority']['town']) ? $array['firmInfo']['registrationAuthority']['town'] : null;
                $create['street'] = isset($array['firmInfo']['registrationAuthority']['street']) ? $array['firmInfo']['registrationAuthority']['street'] : null;
                $create['lat'] = isset($array['firmInfo']['registrationAuthority']['lat']) ? $array['firmInfo']['registrationAuthority']['lat'] : null;
                $create['lng'] = isset($array['firmInfo']['registrationAuthority']['lng']) ? $array['firmInfo']['registrationAuthority']['lng'] : null;
                $create['lx'] = isset($array['firmInfo']['enterpriseType']) ? $array['firmInfo']['enterpriseType'] : null;
                $create['hangye'] = isset($array['firmInfo']['industry']) ? $array['firmInfo']['industry'] : null;
                $create['state'] = isset($array['firmInfo']['manageStatus']) ? $array['firmInfo']['manageStatus'] : null;
                $create['created_at'] = now();
                $create['updated_at'] = now();
                DB::connection()->table('entities')->insert($create);
                $this->info('entities insert is success name is ' . $create['name']);
            }
        }
    }


    /**
     * @param string table_name
     */
    protected function createItemsTable(string $table_name)
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

                $table->index('task_id', 'task_id');
                $table->index(['nid', 'platform_id', 'seller_id']);
                $table->index('classify', 'classify');
                $table->index('item_url', 'item_url');

                $table->timestamps();
            });
        }
    }

    /**
     * @return array
     */
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

    /**
     * @param array $array
     * @return array
     */
    protected function getPCD(array $array = []): array
    {
        $array[] = [
            'exists' => [
                'field' => 'viewSalesVolumeF',
            ],
        ];
        $array[] = [
            'exists' => [
                'field' => 'commentCountF',
            ],
        ];
        $pcd = config('szkj.pcd');
        foreach ($pcd as $k => $v) {
            if (!empty($v)) {
                $array[] = [
                    'term' => [
                        "firmInfo.registrationAuthority.{$k}" => [
                            'value' => $v,
                        ],
                    ],
                ];
            }
        }
        return array_values($array);
    }


    /**
     * 清理ES上的scroll_id 释放内存空间
     */
    protected function clear()
    {
        $this->client->clearScroll([
            'scroll_id' => $this->scroll_id,
        ]);
    }
}