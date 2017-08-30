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
class RuleModel extends BaseModel{
    protected $tableName = 'auth_rule';

    protected $_validate = array(
        //必填验证
        array('name','require','验证的规则必须',0,'',1),
        //唯一性验证
        array('name','','验证的规则已存在',0,'unique',1),
        //其他验证
        );

    protected $_auto = array();

    public function getMenu($pid = 0, $menu_type = 0){
        $m_type = 0;
        if ($m_type) {
            $m_type = $menu_type;
        } else {
            $m_type = D('Users', 'Logic')->getDefaultMenuType();
        }
        $result = $this->where(array('is_menu' =>1, 'pid' => $pid, 'menu_type' => array('in',array(0,$m_type))))->order('sort desc')->select();
        return $result;
    }

    /**
     * 添加权限
     */
    public function addData($data){
        // 对data数据进行验证
        if (!$data = $this->create($data, 1)) {
            // 验证不通过返回错误
            $res['code'] = 0;
            $res['message'] = $this->getError();
        } else {
            // 验证通过
            $res['code'] = 1;
            $res['id'] = $this->add($data);
        }
        return $res;
    }

    /**
     * 修改权限
     */
    public function editData($map,$data){
        // 对data数据进行验证
        if (!$data = $this->create($data)) {
            // 验证不通过返回错误
            $res['code'] = false;
            $res['message'] = $this->getError();
        } else {
            // 验证通过
            $result = $this
                ->where(array($map))
                ->save($data);
            $res['code'] = true;
            $res['message'] = $result;
        }
        return $res;
    }
}