<?php
/**
 * Creator htm
 * Created by 2020/11/2 14:21
 **/


return [
    /*
    |--------------------------------------------------------------------------
    |   爬虫配置地区 省市区
    |--------------------------------------------------------------------------
    |   province,city,district
    */
    'pcd'                 => [

        'province' => '',

        'city' => '',

        'district' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    |   elasticsearch hosts
    |--------------------------------------------------------------------------
    */
    'elasticsearch-hosts' => [],

    /*
    |--------------------------------------------------------------------------
    |   assignment链接地址
    |--------------------------------------------------------------------------
    */
    'assignment-host'     => '',

    /*
    |--------------------------------------------------------------------------
    |   是否判断违规
    |--------------------------------------------------------------------------
    */
    'check-violation'     => true,

    /*
    |--------------------------------------------------------------------------
    |   优先等级 1-20
    |--------------------------------------------------------------------------
    */
    'priority'            => 8,

    /*
    |--------------------------------------------------------------------------
    |   rabbitmq config 消息队列相关配置
    |--------------------------------------------------------------------------
    */
    'rabbitmq'            => [
        //数据回推队列
        'data-push-queue' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | route settings 路由相关设置
    |--------------------------------------------------------------------------
    |
    */
    'route'               => [

        'prefix' => env('API_PREFIX', 'api'),

        'namespace' => 'Szkj\\Rbac\\Controllers',

        'middleware' => ['auth:api', 'szkj.rbac'],
    ],

];