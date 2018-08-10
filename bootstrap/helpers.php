<?php

use Illuminate\Support\Str;

// 辅助函数
if (!function_exists('route_class')) {
    function route_class()
    {
        return str_replace('.', '-', Route::currentRouteName());
    }
}
if (!function_exists('parse_xml')) {
    function parse_xml($xml)
    {
        // 用 simplexml_load_string 函数初步解析 XML，返回值为对象，再通过 normalize_xml 函数将对象转成数组
        return normalize_xml(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_NOCDATA | LIBXML_NOBLANKS));
    }
}
if (!function_exists('normalize_xml')) {
    // 将 XML 解析之后的对象转成数组
    function normalize_xml($obj)
    {
        $result = null;
        if (is_object($obj)) {
            $obj = (array) $obj;
        }
        if (is_array($obj)) {
            foreach ($obj as $key => $value) {
                $res = normalize_xml($value);
                if (('@attributes' === $key) && ($key)) {
                    $result = $res;
                } else {
                    $result[$key] = $res;
                }
            }
        } else {
            $result = $obj;
        }
        
        return $result;
    }
}

// 格式化数字
if (!function_exists('numberFormat')) {
    function numberFormat($number)
    {
        return str_replace('.00', '', $number);
    } 
}

if (!function_exists('getImageUrlAttribute')) {
    function getImageUrlAttribute($image)
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }
        return \Storage::disk('admin')->url($image);
    }
}