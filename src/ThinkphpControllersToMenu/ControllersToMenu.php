<?php

namespace Hwl\ThinkphpControllersToMenu;

use Hwl\ThinkphpControllersToMenu\FileManage\FolderFilesToClassMethods as FolderToMethods;
use zpt\anno\Annotations;
/**
 * 根据控制器转为菜单数组
 * Class ControllersToMenu
 * @package Hwl\ThinkphpControllersToMenu
 * @author Hwl<weigewong@gmail.com>
 */
class ControllersToMenu{
    /**
     * 控制器文件夹路径
     * 默认为当前文件夹
     * @var string
     */
    protected $controllerFolderPath = './';
    //项目命名空间,默认为后台Admin\Controller
    protected $tpProjectNamespace = 'Admin\\Controller\\';

    /**
     * 要过滤的类名
     * 该处的类名,即不扫描该文件路径,不放进控制器列表里,则不会在菜单栏分组里显示
     * @var array
     */
    protected $notClass       = array();

    /**
     * 要过滤的方法名,要考虑到继承,所以要将父类的元素也考虑进去
     * @var array
     */
    protected $notClassMethod = array();
    /**
     * 菜单文档注释解析的key键,默认为MenuOpts
     * @var string
     */
    protected $menuAnnotationKey = 'MenuOpts';

    /**
     * 初始化控制器管理生成菜单数组类
     * @param string $folderPath 控制器文件夹路径
     */
    public function __construct($folderPath){
        $this->controllerFolderPath = $folderPath;
    }

    /**
     * 获取生成的菜单
     * 返回数组array('FolderMenuName' => array('MethodMenu','MethodMenu'))
     * @return array
     */
    public function getMenu(){
        $fmc = new FolderToMethods($this->controllerFolderPath);
        $fmc->setNamespace($this->tpProjectNamespace);
        $controllerList = $fmc->getControllerList(true,$this->notClass);

        $menus          = array();

        foreach($controllerList as $key => $controller){
            $menus[$key] = array();

            $ctrlClassNames = explode('\\', $controller->getName());
            $ctrlName    = array_pop($ctrlClassNames);
            $notClass    = $this->notClassMethod;//"BaseController,AuthController,Controller";

            $ctrlAnnotations = new Annotations($controller);
            //如果菜单的注释信息,则检查该类及方法
            if($ctrlAnnotations->hasAnnotation($this->menuAnnotationKey)){
                //可以设定一些默认的值
                $defaultMenuInfo = array(
                    'menuName' => '',
                    'menuUrl'  => '',
                    'ctrlName' => $ctrlName,
                );
                //菜单信息
                $menuInfo = $ctrlAnnotations[$this->menuAnnotationKey];
                //如果有ignore属性,则不检查该类及方法
                if(isset($menuInfo['ignore']) && $menuInfo['ignore']){
                    continue;
                }

                $menuInfo = array_merge($defaultMenuInfo,$menuInfo);
                //如果没有设置菜单名,则使用控制器名
                $menuInfo['menuName']    = $menuInfo['menuName'] == '' ? $ctrlName : $menuInfo['menuName'];
                //菜单链接,如果没有指定,可以由模板处来判断,取控制器名,用U方法生成
                //$menuInfo['menuUrl']     = $menuInfo['menuUrl'];
                //二级菜单,即方法
                $menuInfo['subMenus'] = $fmc->getClassMethodsDocs2Array($controller,\ReflectionMethod::IS_PUBLIC , $notClass,$ctrlName);
                $menus[$key] = $menuInfo;
            }

        }

        return $menus;
    }

    /**
     * 设置控制器文件夹路径
     * @param string $path 文件夹路径
     */
    public function setControllerFolderPath($path){
        $this->controllerFolderPath = $path;
    }
    /**
     * 设置过滤的类名及方法
     * @param string $className  类名
     * @param array  $methods    方法名称数组,如何为空数组,则表示过滤整个类 array('methodName','methodName')
     */
    public function setNotClassMethod($className,$methods = array()){
        $this->notClassMethod[$className] = $methods;
    }

    /**
     * 设置过滤的类名(菜单栏)
     * @param string $className
     */
    public function setNotClass($className){
        if(!in_array($className, $this->notClass)){
            $this->notClass[] = $className;
        }
    }

    /**
     * 设置菜单注释关键词
     * @param string $keyName
     * @return bool
     */
    public function setMenuAnnotationKey($keyName){
        if($keyName && $keyName != ''){
            $this->menuAnnotationKey = $keyName;
            return true;
        }
        return false;
    }

    /**
     * 设置命名空间
     */
    public function setNamespace($namespace){
        $this->tpProjectNamespace = $namespace;
    }

}