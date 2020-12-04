<?php
/**
 * Creator htm
 * Created by 2020/12/1 10:22
 **/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViolationTable extends Migration{
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
        if(!Schema::hasTable($this->tableName('violations'))){
            Schema::create($this->tableName('violations'), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('platform_id')->default(0)->comment('平台ID');
                $table->string('platform_tag',32)->comment('平台标签');
                $table->integer('data_id')->default(0)->comment('数据id');
                $table->integer('task_id')->default(0)->comment('任务id');
                $table->string('name',255)->comment('违规名称');
                $table->string('word',255)->nullable()->comment('违规关键词');
                $table->string('violation_type',255)->nullable()->comment('违规类型');
                $table->tinyInteger('status')->default(0)->comment('是否是违规 0-等待 1-是 2-不是');
                $table->tinyInteger('pull')->default(0)->comment('0未推送1已推送');
                $table->tinyInteger('machine')->default(0)->comment('0机器判断 1人工判断');


                $table->index(['status','pull','machine']);
                $table->index(['platform_id','platform_tag','data_id','task_id']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable($this->tableName('violation_type'))){
            Schema::create($this->tableName('violation_type'),function (Blueprint $table){
               $table->id();
               $table->string('name',100)->comment('违规类型');

               $table->unique('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName('violations'));
        Schema::dropIfExists($this->tableName('violation_type'));

    }
}