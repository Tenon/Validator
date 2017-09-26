<?php

if (!function_exists('array_get')) {
    /** 数组截取
     * @param array $source
     * @param mixed $need
     * @param mixed $default
     * @return mixed
     */
    function array_get(array $source, $need, $default = null)
    {
        $ret = [];
        if (is_string($need) || is_integer($need)) {
            return $source[$need] ?? $default;
        }
        elseif (is_array($need)) {
            array_walk($need, function($item) use ($source, &$ret) {
                if (isset($source[$item])) {
                    $ret[$item] = $source[$item];
                }
            });
            $ret = $ret ? $ret : $default;
        }
        return $ret;
    }
}