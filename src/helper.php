<?php

/**
 * 下划线转首字母大写驼峰
 */
function convertUnderline($str)
{
    $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
        return strtoupper($matches[2]);
    }, $str);
    return ucfirst($str);
}
