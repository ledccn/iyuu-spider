<?php
/**
 * 测试路由枚举
 */

use Iyuu\Spider\Contract\Route;

require_once dirname(__DIR__) . '/vendor/autoload.php';
global $argv;

$value = ucwords(strtolower($argv[1] ?? ''));
var_dump($value);
var_dump(Route::hasName($value));
print_r(array_column(Route::cases(), 'value', 'name'));
