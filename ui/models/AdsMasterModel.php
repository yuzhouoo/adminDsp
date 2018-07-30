<?php
/**
 * @获取广告主数据
 */
class AdsMasterModel extends CI_Model{
	
	public function __construct(){
		parent::__construct();
		
		$this->load->library('DbUtil');
	}

	public function getList($arrParams){
		if(isset($arrParams['masterName']) && !empty($arrParams['masterName'])){
			$sqlWhere = 'company like "%'.$arrParams['masterName'].'%" OR contact_person like "%'.$arrParams['masterName'].'%"';
		}else{
			$sqlWhere = '';
		}

		$totalWhere = array(
			'select' => 'count(*)',
			'where' => $sqlWhere,
		);
		
		if($arrParams['current'] == 1){
			$startKey = 0;
		}else{
			$startKey = ($arrParams['current'] - 1) * $arrParams['pageSize'];
		}

		$where = array(
			'select' => '*',
			'where' => $sqlWhere,
			'order_by' => 'create_time DESC',
			'limit' => $startKey.','.$arrParams['pageSize'],
		);

		if($sqlWhere == '' || empty($sqlWhere)){
			unset($totalWhere['where']);
			unset($where['where']);
		}

		$total = $this->dbutil->getAccInfo($totalWhere);
		if(!$total || !$total[0]['count(*)']){
			return [
				'list' => [],
				'pagination' => [
					'total' => 0,
					'current' => 0,
					'pageSize' => 0,
				],
			];
		}

		$list = $this->dbutil->getAccInfo($where);
		if(!$list){
			return [
				'list' => [],
				'pagination' => [
					'total' => 0,
					'current' => 0,
					'pageSize' => 0,
				],
			];
		}
		
		return [
			'list' => $list,
			'pagination' => [
				'total' => (int)$total[0]['count(*)'],
				'current' => (int)$arrParams['current'],
				'pageSize' => (int)$arrParams['pageSize'],
			],
		];
	}

	public function getMasterInfo($params){
		$where = array(
			'select' => '*',
			'where' => 'account_id="'.$params.'"',
		);

		$info = $this->dbutil->getAccInfo($where);
		if(!$info){
			return [];
		}

		return $info[0];
	}

	public function modifyMasterStatus($arrParams){
		$where = array(
			'check_status' => $arrParams['check_status'],
			'where' => 'account_id="'.$arrParams['account_id'].'" AND check_status="1"',
		);

		if($arrParams['check_status'] == '3'){
			$where['check_msg'] = $arrParams['check_msg'];
		}

		$res = $this->dbutil->udpAccInfo($where);
		
		if($res['code'] == 0){
			return true;
		}

		return false;
	}
}
?>
