<?php
namespace Admin\Controller;

use Admin\Controller\BaseController;

/**
 * 默认控制器
 * Class IndexController
 * @package Admin\Controller
 * @MenuOpts(menuName=AdminHome)
 * @author Hwl<weigewong@gmail.com>
 */
class IndexController extends BaseController{
    /**
     * 后台首页
     * @MenuOpts(menuName=Dashboard)
     */
    public function index(){

    }

    /**
     * 我是需要被忽略的
     * @MenuOpts(menuName=我是需要被忽略的,ignore=true)
     */
    public function ignore(){

    }


}