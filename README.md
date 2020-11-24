


## Installing

```shell
$ composer require szkj/install 
```

## Usage
安装初始化
```shell
$ php artisan szkj:install
```
请配置 config.szkj
```shell
'pcd'=> [
         //省
        'province' => '',
        //市
        'city' => '',
        //区
        'district' => '',
    ]
````
请配置 config.api
```shell
  'errorFormat' => [
        'message' => ':message',
        'errors' => ':errors',
//        'code' => ':code',
        'code' => ':status_code',
        'debug' => ':debug',
    ],
```
## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/szkj/install/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/szkj/install/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT