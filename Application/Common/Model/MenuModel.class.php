<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/5/14
 * Time: 14:47
 * 菜单基础操作模型
 */
namespace Common\Model;

class MenuModel extends BaseModel {
//     // 自动验证
//     protected $_validate=array(
//         array('title','require','标题必须',0,'',3), // 验证字段必填
// //        array('litpic','require','缩略图必须',0,'',3), // 验证字段必填
//         array('picture','require','图片至少为一',0,'',3), // 验证字段必填
//         array('creat_time','require','创建时间必须',0,'',1), // 验证字段必填
//         array('show_type','require','展示方式必须',0,'',3), // 验证字段必填
//         array('title','','标题已经存在',0,'unique',1), // 验证字段必填
//     );
//
//     // 自动完成
//     protected $_auto=array(
//         array('creat_time','today_day',1,'function'), // 对date字段在新增的时候写入当前时间戳
//     );

    public function addData($data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            $res['code']=false;
            $res['message']=$this->getError();
        }else{
            // 验证通过
            $res['code']=true;
            $res['message']=$this->add($data);
        }
        return $res;
    }

    public function editData($map,$data)
    {
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