<?php
/**
 * Creator htm
 * Created by 2020/11/6 14:12
 **/

namespace Szkj\Install\Seeder;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    protected $tag = 'items';

    protected $cate = '电商平台';

    public function run()
    {
        $insert=[
            ['id' => 1, 'name' => '天猫'],
            ['id' => 2, 'name' => '淘宝'],
            ['id' => 3, 'name' => '苏宁易购'],
            ['id' => 4, 'name' => '京东'],
            ['id' => 5, 'name' => '阿里巴巴'],
        ];
        foreach ($insert as $k => $v) {
            if (!DB::connection($this->getConnection())->table('platforms')->where('id', $v['id'])->count()) {
                $v['tag'] = $this->tag;
                $v['cate'] = $this->cate;
                $v['created_at'] = now();
                $v['updated_at'] = now();
                DB::connection($this->getConnection())->table('platforms')->insert($v);
            }
        }
    }

    /**
     * @return string
     */
    public function getConnection(): string
    {
        return config('database.default');
    }
}