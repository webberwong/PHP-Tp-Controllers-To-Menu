<?php
/**
 * 测试试引导
 * @author Hwl<weigewong@gmail.com>
 */

require __DIR__ . '/../vendor/autoload.php';

//添加自动加载控制器类的处理,用来模仿Thinkphp控制器环境;
//估计可能会用上,也可能用不上

spl_autoload_register(function($class){
    $className = ltrim($class,'Admin\\Controller\\');
    $classFilePath = __DIR__ . '/ThinkphpControllersToMenu/Controller/' . $className . '.class.php';
    if(is_file($classFilePath)){
        include $classFilePath;
    }
});
