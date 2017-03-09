# laravel-baidutongji

>请参考百度统计文档使用,本项目只是laravel集成包
## 安装

1. 安装包文件

  ```shell
  composer require mushan/baidu-tongji:"1.0"
  ```

## 配置

### Laravel 应用

1. 注册 `ServiceProvider`:

  ```php
  Mushan\BaiduTongji\BaiduTongjiServiceProvider::class,
  ```

2. 创建配置文件：

  ```shell
  php artisan vendor:publish
  ```

3. 修改应用根目录下的 `config/baidu_tongji.php` 中对应的项即可；

## 使用

```php
<?php

namespace App\Http\Controllers;

use

class SiteController extends Controller
{

    public function index()
    {
        $baiduTongji=resolve('BaiduTongji');

        $today=date('Ymd');
        $yesterday=date('Ymd',strtotime('yesterday'));
        $result=$baiduTongji->getData([
            'method' => 'trend/time/a',
            'start_date' => $today,
            'end_date' => $today,
            'start_date2' => $yesterday,
            'end_date2' => $yesterday,
            'metrics' => 'pv_count,visitor_count',
            'max_results' => 0,
            'gran' => 'day',
        ]);
        dd($result);
    }
}
```


