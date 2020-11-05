<?php
namespace Lyue\LaravelModel;
/**
 * Created by PhpStorm.
 * User: linyue
 * Date: 2020/11/5
 * Time: 10:57
 */
use Illuminate\Support\ServiceProvider as SP;

class ServiceProvider extends SP
{
    public function boot(){
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Lyue\LaravelModel\Commands\CreateModels::class,
                \Lyue\LaravelModel\Commands\UpdateModels::class
            ]);
        }
    }
}
