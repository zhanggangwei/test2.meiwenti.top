<?php 
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/20
 * Time: 16:52
 */
namespace Common\Logic;

class GeneralCompanyLogic extends \Think\Model{

	// 记录常用的关联公司
	public function log($relation_company, $type = 0){
		$db = M('general_company');
		$company = session('company_id');
		//判断公司关系是否存在
		$info = $db->where(array('company' => $company, 'general_company' => $relation_company, 'type' => $type))->find();
		// 判断是否合作
		
		// 组合数据
		$data = array(
			'company'          => $company,
			'general_company'  => $relation_company,
			// 'state'            => $state,
			'type'             => $type,
			);
			
		if ($info) {
			$data['id']    = $info['id'];
			$data['count'] = $info['count'] + 1;
			$db->save($data);
		} else {
			$db->add($data);
		}
	}

}