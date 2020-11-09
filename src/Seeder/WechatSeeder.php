<?php
/**
 * Creator htm
 * Created by 2020/11/6 14:21
 **/

namespace Szkj\Install\Seeder;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WechatSeeder extends Seeder
{
    protected $tag = 'wechat';

    protected $cate = '微信公众号';

    public function run()
    {
        $insert=[
            ['id' => 15, 'name' => '微信公众号'],
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