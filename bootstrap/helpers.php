<?php
// 辅助函数
if (!function_exists('route_class')) {
    function route_class()
    {
        return str_replace('.', '-', Route::currentRouteName());
    }
}