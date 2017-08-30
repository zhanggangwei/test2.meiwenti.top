<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/3/24
 * Time: 16:49
 */

class menuLogic {
    // 获得下级菜单
    public function getMenu($pid = 0){
        $menu1 = $this->where(array('is_menu' =>1, 'pid' => $pid))->order('sort desc')->select();
        $result = array();
        $auth = new \Think\Auth();
        foreach ($menu1 as $key => $value){
            if ($auth->check($value['name'], $this->uid)) {
                $result[$key]['name']  = $value['title'];
                $result[$key]['id']    = $value['id'];
                $result[$key]['url']   = $value['name'];
            }
        }
        return $result;
    }
}