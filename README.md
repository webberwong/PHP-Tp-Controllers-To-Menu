Thinkphp 控制器转为菜单数组类库
============================================
根据Thinkphp控制器里写的注释来转为菜单数组,供后台功能菜单使用.<br>
只适用于 *Thinkphp 3.2* 版本<br>

## 使用说明
一般是针对于后台,就使用默认的配置就行,初始化的时候,传入项目文件夹的路径即可
首先,记得先
composer update 下载依赖的库并自动生成composer的自动加载类
#### 简单示例
```php
//加入命名空间
use Hwl\ThinkphpControllersToMenu\ControllersToMenu;

$controllerFolderPath = __DIR__ . '/Controller';
//初始化并赋值上路径
$ctm  = new ControllersToMenu($controllerFolderPath);

//获取菜单
$menu = $ctm->getMenu();
```
#### 更改命名空间,即不是默认的Admin项目
默认的命名空间是Admin\Controller
```php
//更改命名空间,注意反斜杠的转义,命名空间的形式
$ctm->setNamespace('Home\\Controller\\');
$menu = $ctm->getMenu();
```

#### 未测试的功能
这些功能是以前以前的版本有设计该功能,并通过测试的,但在该版本里,是保留,未进行测试的  
功能列表:  

* 设置过滤的类
```php
    //该版本已经增加的在注释里进行过滤,但由于继承的父类,所以仍保留这个功能,未进行测试
    /**
     * 设置过滤的类名(菜单栏)
     * @param string $className
     */
    $ctm->setNotClass($className);
```
* 设置过滤的类及方法
```php
    //该版本已经增加的在注释里进行过滤,但由于继承的父类及父类的方法,所以仍保留这个功能,未进行测试
    /**
     * 设置过滤的类名及方法
     * @param string $className  类名
     * @param array  $methods    方法名称数组,如何为空数组,则表示过滤整个类 array('methodName','methodName')
     */
    $ctm->setNotClassMethod($className,$methods = array());
```
<br>
其他的请查看代码文件