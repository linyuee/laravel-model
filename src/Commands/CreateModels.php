<?php

namespace Lyue\LaravelModel\Commands;

use Illuminate\Console\Command;

class CreateModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建model文件';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //获取当前所有表
        $tables = array_map('reset', \DB::select('SHOW TABLES'));

        //获取模板文件
        $template = file_get_contents(dirname(__FILE__) . '/../stubs/model.stub');
        $template_method = file_get_contents(dirname(__FILE__) . '/../stubs/model_method.stub');

        //model文件目录
        $model_path = app_path() . '/Models';


        foreach ($tables as $key => $v) {
            $class_name = convertUnderline($v);
            $file_name = $class_name . '.php';
            $file_path = $model_path . '/' . $file_name;

            //判断文件是否存在,存在则跳过
            if (file_exists($file_path)) {
                continue;
            }

            //查询所有字段
            $columns_ide = '';
            $columns = \DB::select('SHOW COLUMNS FROM `' . $v . '`');
            foreach ($columns as $vv) {

                if (strpos($vv->Type, "int") !== false)
                    $type = 'int';
                else if (strpos($vv->Type, "varchar") !== false || strpos($vv->Type, "char") !== false || strpos($vv->Type, 'blob') || strpos($vv->Type, "text") !== false) {
                    $type = "string";
                } else if (strpos($vv->Type, "decimal") !== false || strpos($vv->Type, "float") !== false || strpos($vv->Type, "double") !== false) {
                    $type = "float";
                }
                else{
                    $type = 'string';
                }

                $columns_ide .= ' * @property ' . $type . ' $' . $vv->Field.PHP_EOL;
            }

            $columns_ide.=' *';
            $template_temp = $template;
            $source = str_replace('{{class_name}}', $class_name, $template_temp);
            $source = str_replace('{{table_name}}', $v, $source);
            $source = str_replace('{{ide_property}}', $columns_ide, $source);
            $source_method=str_replace('{{class_name}}', '\App\Models\\'.$class_name, $template_method);
            $source = str_replace('{{ide_method}}', $source_method, $source);

            //写入文件
            if (!is_dir($model_path)) {
                $res = mkdir($model_path, 0755, true);
                if (!$res) $this->error('目录' . $model_path . ' 无法写入文件,创建' . $class_name . ' 失败');
            }

            if (file_put_contents($file_path, $source)) {
                $this->info($class_name . '添加类成功');
            } else {
                $this->error($class_name . '类写入失败');
            }

        }

    }
}
