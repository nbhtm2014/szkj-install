<?php
/**
 * Creator htm
 * Created by 2020/11/2 13:52
 **/
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectionTable extends Migration
{

    /**
     * @return string
     */
    public function getConnection() :string
    {
        return config('database.default');
    }

    /**
     * @return string
     */
    public function getPrefix() : string
    {
        return config('database.connections.'.$this->getConnection().'.prefix');
    }

    /**
     * @param string $name
     * @return string
     */
    public function tableName(string $name):string{
        return $name;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * create tasks table
         */
        Schema::create($this->tableName('tasks'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title',255)->comment('采集标题');
            $table->string('platform_id',100)->comment('采集平台id');
            $table->text('keywords')->comment('采集关键词');
            $table->tinyInteger('status')->default(0)->comment('任务状态');
            $table->integer('user_id')->comment('用户id');
            $table->tinyInteger('pull_status')->default(0)->comment('推送状态');
            $table->string('pcd',100)->comment('省市区');
            $table->tinyInteger('type')->default(0)->comment('任务类型 默认0临时任务，1月度任务');
            $table->string('system_id',255)->nullable()->comment('系统id');
            $table->string('es_id',255)->nullable()->comment('es_id');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * create platforms table
         */
        Schema::create($this->tableName('platforms'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',255)->comment('平台名称');
            $table->string('cate',255)->comment('平台分类');
            $table->string('tag',255)->comment('标签');
            $table->timestamps();
        });


        /**
         * create entities tables
         */
        Schema::create($this->tableName('entities'),function (Blueprint $table){
            $table->bigIncrements('id');
            $table->string('name',100)->comment('公司名称');
            $table->char('credit_no',20)->nullable()->comment('信用代码/工商注册号');
            $table->string('regist_no',100)->nullable()->comment('注册号');
            $table->string('address',255)->nullable()->comment('公司地址');
            $table->string('frdb',200)->nullable()->comment('法人代表');
            $table->string('djjg',200)->comment('登记机关');
            $table->string('province',20)->nullable()->comment('省');
            $table->string('city',20)->nullable()->comment('市');
            $table->string('district',20)->nullable()->comment('区');
            $table->string('town',50)->nullable()->comment('镇');
            $table->string('street',20)->nullable()->comment('街道');
            $table->string('website',100)->nullable()->comment('网站');
            $table->string('lx',100)->nullable()->comment('公司类型');
            $table->string('hangye',100)->nullable()->comment('公司行业');
            $table->string('lat',100)->nullable();
            $table->string('lng',100)->nullable();
            $table->string('state',50)->nullable()->comment('经营状态/登记状态');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * create items tables
         */
        Schema::create('items', function (Blueprint $table) {
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

        /**
         * create shops tables
         */
        Schema::create('shops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('platform_id')->comment('平台id');
            $table->string('shop_id', 50)->comment('店铺id');
            $table->string('name', 100)->comment('店铺名称');
            $table->string('nick', 255)->nullable()->comment('昵称');
            $table->string('shop_url', 255)->nullable()->comment('店铺链接');
            $table->string('licence_url', 255)->nullable()->comment('执照链接');
            $table->string('permit_url', 255)->nullable()->comment('许可证链接');
            $table->string('credit_code', 255)->nullable()->comment('信用代码');
            $table->string('company', 255)->nullable()->comment('公司名称');
            $table->string('member_id', 255)->nullable();
            $table->string('seller_id', 255)->nullable();
            $table->string('item_user_id', 255)->nullable();
            $table->timestamps();
        });

        /**
         * create wechat tables
         */
        Schema::create('wechat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('task_id')->default(0)->comment('任务id');
            $table->string('wechat_id', 255)->nullable();
            $table->string('wechat_nick', 255)->nullable();
            $table->string('effect', 255)->nullable();
            $table->string('company', 255)->nullable()->comment('公司名称');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName('tasks'));
        Schema::dropIfExists($this->tableName('platforms'));
        Schema::dropIfExists($this->tableName('entities'));
        Schema::dropIfExists($this->tableName('items'));
        Schema::dropIfExists($this->tableName('shops'));
        Schema::dropIfExists($this->tableName('wechat'));
    }

}