<?php
include __DIR__ . '/../../bootstrap.php';

use Hwl\ThinkphpControllersToMenu\FileManage\FolderFilesToClassMethods as FolderToMethods;

/**
 * 测试文件夹文件转成类方法
 * Class FolderFilesToClassMethodsTest
 */
class FolderFilesToClassMethodsTest extends \PHPUnit_Framework_TestCase{
    protected $fftcm = null;
    public function __construct(){
        $path = __DIR__ . '/../Controller';
        $this->fftcm = new FolderToMethods($path);
    }

    /**
     * 测试得到的控制器文件
     */
    public function testGetControllerFileLists(){
        $list = $this->fftcm->getControllerFileLists();
        //遍历获取到的文件列表
        //fwrite(STDOUT,'本次得到的控制器文件为:' . PHP_EOL);
        //var_dump($list);
        $this->assertEquals(true, count($list) > 0);
    }
}