<?php
/**
 * Creator htm
 * Created by 2020/11/6 13:52
 **/

namespace Szkj\Install\Seeder;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        if (!DB::connection($this->getConnection())->table('users')->where('superadmin', 1)->count()) {
            DB::connection($this->getConnection())->table('users')->insert([
                'name'       => 'superadmin',
                'email'      => 'szkj@szkj.com',
                'password'   => Hash::make('north4'),
                'superadmin' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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