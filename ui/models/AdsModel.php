<?php

class AdsModel extends CI_Model{
	public function __construct(){
		parent::__construct();

		$this->load->library("DbUtil");
	}

	public function getList($arrParams){
		if(isset($arrParams['proName']) && !empty($arrParams['proName'])){
			$sqlWhere = 'pro_name like "%'.$arrParams['proName'].'%"';
		}else{
			$sqlWhere = '';
		}

		if(isset($arrParams['date']) && !empty($arrParams['date']) && strtotime($arrParams['date'])){
			$startDate = strtotime($arrParams['date']);
			$endDate = $startDate+86399;
			$sqlWhere = !empty($sqlWhere) ? $sqlWhere.' AND create_time >= '.$startDate.' AND create_time <= '.$endDate : 'create_time >= '.$startDate.' AND create_time <= '.$endDate;
		}
		$totalWhere = array(
			'select' => 'count(*)',
			'where' => $sqlWhere,
		);
		
		if($arrParams['current'] == 1){
			$startKey = '0';
		}else{
			$startKey = ($arrParams['current'] - 1) * $arrParams['pageSize'];
		}

		$where = array(
			'select' => '*',
			'where' => $sqlWhere,
			'order_by' => 'create_time ASC',
			'limit' => $startKey.','.$arrParams['pageSize'],
		);

		if($sqlWhere == '' || empty($sqlWhere)){
			unset($totalWhere['where']);
			unset($where['where']);
		}

		$total = $this->dbutil->getProInfo($totalWhere);
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

		$list = $this->dbutil->getProInfo($where);
		
		if(!$list){
			return [
				'list' => [],
				'pagination' => [
					'total' => 0,
					'pageSize' => 0,
					'current' => 0,
				],
			];
		}

		return [
			'list' => $list,
			'pagination' => [
				'total' => (int)$total[0]['count(*)'],
				'pageSize' => (int)$arrParams['pageSize'],
				'current' => (int)$arrParams['current'],
			],
		];
	}

	public function getInfo($arrParams){
		$where = array(
			'select' => '*',
			'where' => 'pro_id = '.$arrParams['pro_id'].' AND account_id = '.$arrParams['account_id'],
		);

		$res = $this->dbutil->getProInfo($where);
		if(!$res){
			return [];
		}

		return $res[0];
	}

	public function modifyAdsStatus($arrParams){
		$where = array(
			'where' => 'account_id='.$arrParams['account_id'].' AND pro_id='.$arrParams['pro_id'],
		);
		if(isset($arrParams['audit_status']) && !empty($arrParams['audit_status'])){
			$where['audit_status'] = $arrParams['audit_status'];
			$where['where'] = $where['where'].' AND audit_status=1';
			
			if($arrParams['audit_status'] == '3'){
				$where['check_msg'] = $arrParams['check_msg'];
			}
		}

		if(isset($arrParams['pro_status']) && !empty($arrParams['pro_status'])){
			$where['pro_status'] = $arrParams['pro_status'];
			$where['where'] = $where['where'].' AND audit_status=2';
		}
		
		if(isset($arrParams['running_status']) && !empty($arrParams['running_status'])){
			$where['running_status'] = $arrParams['running_status'];
			$where['where'] = $where['where'].' AND audit_status=2';
		}

		$res = $this->dbutil->udpProInfo($where);
		
		if($res['code'] == 0){
			return true;
		}

		return false;
	}
}

?>
