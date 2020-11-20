<?php
/**
 * Creator htm
 * Created by 2020/11/2 13:52
 **/
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreasTable extends Migration
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
        Schema::create($this->tableName('areas'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',255)->comment('名称');
            $table->string('tag',50)->comment('标签');
            $table->index(['name','tag']);
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
        Schema::dropIfExists($this->tableName('areas'));
    }

}