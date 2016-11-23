<?php
/**
 * Created by PhpStorm.
 * User: mushan
 * Date: 2016/11/23
 * Time: 16:57
 */
namespace Mushan\BaiduTongji;

use Illuminate\Support\ServiceProvider;

class BaiduTongjiServiceProvider extends ServiceProvider
{
//    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config.php' => config_path('baidu_tongji.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config.php', 'baidu_tongji'
        );
    }
}