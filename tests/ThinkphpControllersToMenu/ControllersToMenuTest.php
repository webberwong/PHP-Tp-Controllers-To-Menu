<?php

include __DIR__ . '/../bootstrap.php';

use Hwl\ThinkphpControllersToMenu\ControllersToMenu;

class ControllersToMenuTest extends \PHPUnit_Framework_TestCase{
    protected $ctmt = null;

    public function __construct(){
        $controllerFolderPath = __DIR__ . '/Controller';
        $this->ctmt = new ControllersToMenu($controllerFolderPath);
        //更改命名空间
        //$this->ctmt->setNamespace('Home\\Controller\\');
    }

    public function testGetMenu(){
        $menu = $this->ctmt->getMenu();
        //测试是否正式解析
        $this->assertEquals(true,is_array($menu));
        //测试是否得到2个值
        $this->assertEquals(2,count($menu));
        //测试Base控制器是否为空
        $this->assertEquals(true,empty($menu['Admin\\Controller\\BaseController']));
        //测试Index控制器的方法数大于0
        $this->assertEquals(true,count($menu['Admin\\Controller\\IndexController']) > 0);
    }
    //测试的时候打印遍历的数组
    public function testReturnArray(){
        $menu = $this->ctmt->getMenu();
        fwrite(STDOUT,'var_dump menu array start ...' . PHP_EOL);
        var_dump($menu);
        fwrite(STDOUT,'var_dump menu array end...' . PHP_EOL);
    }

}