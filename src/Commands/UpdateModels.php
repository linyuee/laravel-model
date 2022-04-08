<?php

namespace Lyue\LaravelModel\Commands;

use Illuminate\Console\Command;

class UpdateModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Models';

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

        //model文件目录
        $model_path = app('path') . '/Models';


        //加载模板
        $template_method = file_get_contents(dirname(__FILE__) . '/../stubs/model_method.stub');

        foreach ($tables as $key => $v) {
            $class_name = convertUnderline($v);
            $file_name = $class_name . '.php';
            $file_path = $model_path . '/' . $file_name;

            //判断文件是否存在,不存在则跳过
            if (!file_exists($file_path)) {
                continue;
            }

            //获取Model文件
            $fp = fopen($file_path, "r+");
            if ($fp) {
                $doc_str = '';
                for ($i = 1; !feof($fp); $i++) {
                    $str = fgets($fp);
                    if (trim($str) == '/**') {
                        $doc_str = $str;
                        for ($i = 1; !feof($fp); $i++) {
                            $str = fgets($fp);
                            $doc_str .= $str;
                            if (trim($str) == '*/') {
                                break 2;
                            }
                        }
                    }
                }
            } else {
                continue;
            }
            fclose($fp);

            //替换类名
            $source_method = str_replace('{{class_name}}', '\App\Model\\' . $class_name, $template_method);

            //查询所有字段
            $columns_ide = '/**' . PHP_EOL;
            $columns_ide .= ' * Model ' . $class_name . PHP_EOL;
            $columns_ide .= ' * ' . PHP_EOL;

            $columns = \DB::select('SHOW COLUMNS FROM `' . $v . '`');
            foreach ($columns as $vv) {

                if (strpos($vv->Type, "int") !== false)
                    $type = 'int';
                else if (strpos($vv->Type, "varchar") !== false || strpos($vv->Type, "char") !== false || strpos($vv->Type, 'blob') || strpos($vv->Type, "text") !== false) {
                    $type = "string";
                } else if (strpos($vv->Type, "decimal") !== false || strpos($vv->Type, "float") !== false || strpos($vv->Type, "double") !== false) {
                    $type = "float";
                } else {
                    $type = 'string';
                }

                $columns_ide .= ' * @property ' . $type . ' $' . $vv->Field . PHP_EOL;
            }

            $columns_ide .= ' *' . PHP_EOL;

            $columns_ide .= '';
            $columns_ide .= $source_method;
            $columns_ide .= ' * @package App\Model' . PHP_EOL;
            $columns_ide .= ' */' . PHP_EOL;

            //把文件doc部分替换成为空
            $file = file_get_contents($file_path);
            $last = str_replace($doc_str, $columns_ide, $file);

            //写入文件
            if (!is_dir($model_path)) {
                $this->error('目录' . $model_path . ' 无法写入文件,创建' . $class_name . ' 失败');
                continue;
            }
            unlink($file_path);
            if (file_put_contents($file_path, $last)) {
                $this->info($class_name . '更新model成功');
            } else {
                $this->error($class_name . '更新model失败');
            }

        }

    }

}
