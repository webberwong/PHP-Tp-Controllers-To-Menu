<?php
namespace Hwl\ThinkphpControllersToMenu\FileManage;

use zpt\anno\Annotations;
use zpt\anno\AnnotationParser;
/**
 * 遍历文件夹里的文件,生成列表
 * 遍历文件里的类,生成列表
 * 由于每次在写thinkphp应该时取后台的控制器和动作时都需要重写一次,觉得挺麻烦,所以写成类,方便下次使用
 * 该类的职责为:生成取类里的方法和注释并将注释解析为数组
 * 设计用于php5.3,测试环境php5.3.10
 * @author Hwl<weigewong@gmail.com>
 */
class FolderFilesToClassMethods{
    /**
     * 控制器的文件夹路径
     * @var null|string
     */
	protected $controllerDirPath  = null;
	/**
	 * 文件夹下的php文件
	 * @var array
	 */
	protected $actionFiles = array();
	/**
	 * 文件夹下的class列表,只保留存在的类列表,存放着类的反射
	 * @var array
	 */
	protected $actionLists = array();
	/**
	 * 从文件名去掉一些关键字符,从而得到类名
	 * @var string
	 */
	protected $removeChar  = '.class.php';
	
	/**
	 * 命名空间
	 * @var string
	 */
	protected $namespace   = 'Admin\\Controller\\';
    /**
     * 菜单文档注释解析的key键,默认为MenuOpts
     * @var string
     */
    protected $menuAnnotationKey = 'MenuOpts';
	/**
	 * 构造函数,实现定义文件夹路径和设定删除文件名关键字
	 * @param string $path
	 * @param string $removeChar
	 */
	public function __construct($path,$removeChar = null,$namespace = 'Admin\\Controller\\'){
		$this->controllerDirPath = rtrim(str_replace('\\', '/', $path),'/');
		if($removeChar) $this->removeChar = $removeChar;
		$this->namespace  = $namespace;
	}
	/**
	 * 获取控制器列表
	 * @param bool $cache            是否使用缓存,默认为使用缓存
	 * @param string|array $notClass 不需要的显示获取的控制器类名
	 * @return array
	 */
	public function getControllerList($cache = true,$notClass = ''){
		$files = $this->getFolderFiles();
		if($cache && !empty($this->actionLists)){
			return $this->actionLists;
		}
		//如果是字符串,则解析为数组
		if(is_string($notClass)){
			$notClass = explode(',', $notClass);
		}else if(!is_array($notClass)){
			$notClass = array();
		}

		$classList = array();
		foreach($files as $filename){			
			$className = $this->getFilePathToClassName($filename);
			
			if(in_array($className,$notClass)) continue;
			
			$className = $this->namespace . $className;
            //fwrite(STDOUT,$className . PHP_EOL);
			if(class_exists($className)){
				$classList[$className] = new \ReflectionClass($className);
			}
		}
		$this->actionLists = $classList;
		return $this->actionLists;
	}
	/**
	 * 通过反射类获取类下的方法和方法的注释
	 * 注:由于该类只是需要获取其他自定义的注释,所以重复的注释只会取一个,如param
	 * @param ReflectionClass $rflc   反射类对象
	 * @param int $access             只获取类里的访问属性的对象,全部为false状态,其他有public:256,参考ReflectionMethod...
	 * @param array $notClass         不要哪个 的方法,在Thinkphp里,有时候我们不需要一个类,或者是一个父类的某个方法 array('className' => array('Method','Method')); 如何className 为一个空的数组,则表示该类全部过滤
     * @param string $ctrlName        指定控制器名,这个值会一直存放在method的菜单栏目属性里
	 * @return array
	 */
	public function getClassMethodsDocs2Array(\ReflectionClass $rflc,$access = \ReflectionMethod::IS_PUBLIC,$notClassMethod = array(),$ctrlName = ''){
		$methods  = $access ? $rflc->getMethods($access) : $rflc->getMethods();
		$array    = array();
		//如果是字符串,则解析为数组
		if(!is_array($notClassMethod)){
			$notClassMethod = array();
		}

        //如果没有指定控制器名称,则需要手动进行获取
        if($ctrlName && $ctrlName != ''){
            $ctrlClassNames = explode('\\', $rflc->getName());
            $ctrlName       = array_pop($ctrlClassNames);
        }


		//遍历类里的得到的所有方法
		foreach($methods as $method){
            $classNames = explode('\\', $method->class);
			$className  = array_pop($classNames);
			$methodName = $method->name;
			
			if(key_exists($className, $notClassMethod)){
				if(count($notClassMethod[$className]) == 0 OR !is_array($notClassMethod[$className])){
					continue;
				}else if(in_array($methodName,$notClassMethod[$className])){
					continue;
				}
			}
            //解析方法的注释
            $methodAnnotations = new Annotations($method);
			if($methodAnnotations->hasAnnotation($this->menuAnnotationKey)){
                //可以设定一些默认的值
                $defaultMenuInfo = array(
                    'menuName' => '',
                    'menuUrl'  => '',
                    //这个类名,还是需要修改一下的.因为继承的原因
                    'ctrlName' => $ctrlName,
                    'methodName' => $methodName,
                );
                //菜单信息
                $menuInfo = $methodAnnotations[$this->menuAnnotationKey];
                //如果有ignore属性,则不检查该类及方法
                if(isset($menuInfo['ignore']) && $menuInfo['ignore']){
                    continue;
                }
                $menuInfo = array_merge($defaultMenuInfo,$menuInfo);
                //如果没有设置菜单名,则使用控制器名
                $menuInfo['menuName']    = $menuInfo['menuName'] == '' ? $methodName : $menuInfo['menuName'];
                //菜单链接,如果没有指定,可以由模板处来判断,取控制器名,用U方法生成
                //$menuInfo['menuUrl']     = $menuInfo['menuUrl'];
                $array[$methodName] = $menuInfo;
            }

		}
		return $array;
	}
	
	/**
	 * 获取Controller文件夹下的PHP控制器文件列表,具有缓存功能
	 * @param bool $cache 是否使用缓存,默认为使用缓存
	 * @return array
	 */
	public function getFolderFiles($cache = true){
		if($cache){
			if(empty($this->actionFiles)){
				$this->actionFiles = $this->scanFolderFiles();
				return $this->actionFiles;
			}else{
				return $this->actionFiles;
			}
		}else{
			$this->actionFiles = $this->scanFolderFiles();
			return $this->actionFiles;
		}
	}

	/**
	 * 获取Controller文件夹里的控制器文件列表
	 * 这里使用glob是想练习一下该函数,实现方法有多种
	 * @return array|false
	 */
	private function scanFolderFiles(){
		if($this->controllerDirPath){
			$array = glob($this->controllerDirPath . "/*.class.php");
			return $array;
		}else{
			return false;
		}
	}
	/**
	 * 通过Thinkphp的命名规则获取控制器的类名
	 * @param string $filepath
	 * @return string
	 */
	private function getFilePathToClassName($filepath){
		return basename($filepath,$this->removeChar);
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
     * 获取匹配的controller文件,主要是提供给测试单元测试用
     * @return array|false
     */
    public function getControllerFileLists(){
        return $this->scanFolderFiles();
    }

    /**
     * 设置命名空间
     */
    public function setNamespace($namespace){
        $this->namespace = $namespace;
    }
}