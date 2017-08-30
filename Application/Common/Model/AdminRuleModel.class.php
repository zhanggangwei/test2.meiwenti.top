<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/3/24
 * Time: 16:37
 */
namespace Common\Model;

/**
 *规则表基础model
 */
class AdminRuleModel extends BaseModel{
    protected $tableName = 'admin_auth_rule';

    protected $_validate = array();

    protected $_auto = array();

    public function getMenu($pid = 0){
        $result = $this->where(array('is_menu' =>1, 'pid' => $pid))->order('sort desc')->select();
        return $result;
    }
}