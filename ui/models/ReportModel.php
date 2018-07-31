<?php
/*accountID当数组key，抑制Notice错误*/
error_reporting(E_ALL & ~E_NOTICE);

/**
 *@数据报表model
 */
class ReportModel extends CI_Model{
	public function __construct(){
		parent::__construct();
		$this->load->library("DbUtil");
	}

	/**
	 * @获取报表数据
	 * level All:所有广告主数据,Acct:单个广告主数据,Ads:单个广告单元数据
	 */
	public function getReportData($arrParams){
		$level = 'All';
		$sqlWhere = "DATE_FORMAT(date,'%Y-%m-%d') >= DATE_FORMAT('".$arrParams['startDate']."','%Y-%m-%d') AND DATE_FORMAT(date,'%Y-%m-%d') <= DATE_FORMAT('".$arrParams['endDate']."','%Y-%m-%d')";

		if(isset($arrParams['type']) && $arrParams['type'] == 'Acct' && !empty($arrParams['accId'])){
			$level = 'Acct';
			$sqlWhere = 'account_id='.$arrParams['accId'].' AND '.$sqlWhere;
		}

		if(isset($arrParams['type']) && $arrParams['type'] == 'Ads' && !empty($arrParams['proId'])){
			$level = 'Ads';
			$sqlWhere = 'pro_id='.$arrParams['proId'].' AND '.$sqlWhere;
		}

		if(isset($arrParams['type']) && $arrParams['type'] == 'Days' && !empty($arrParams['accId'])){
			$level = 'Days';
			$sqlWhere = 'account_id='.$arrParams['accId'].' AND '.$sqlWhere;
		}

		$totalWhere = array(
			'select' => 'count(*)',
			'where' => $sqlWhere,
		);
		
		$total = $this->dbutil->getProData($totalWhere);
		if(!$total || !$total[0]['count(*)']){
			return [
				'list' => [],
				'pagination' => [
					'total' => 0,
					'current' => 0,
					'pageSize' => 0,
				],
				'curve' => [],
			];
		}
		
		if($arrParams['current'] == 1){
			$startKey = '0';
		}else{
			$startKey = ($arrParams['current'] - 1) * $arrParams['pageSize'];
		}

		$where = array(
			'select' => '*',
			'where' => $sqlWhere,
			'order_by' => 'date ASC',
		);
		
		$curveWhere = array(
			'select' => '*',
			'where' => $sqlWhere,
			'order_by' => 'date ASC',
		);

		if(isset($arrParams['type']) && !empty($arrParmas['type'])){
			$where['limit'] = $startKey.','.$arrParams['pageSize'];
		}
	
		$res = $this->dbutil->getProData($where);
		$list = $this->handleReportData($level,$res);
		
		if($level == 'All' || $level == 'Acct'){
			$curveRes = $this->dbutil->getProData($curveWhere);
			$curve = $this->handleReportData('Curve',$curveRes);
		}
		
		if(!$list){
			return [
				'list' => [],
				'pagination' => [
					'total' => 0,
					'pageSize' => 0,
					'current' => 0,
				],
				'curve' => [],
			];
		}

		if($level == 'All' || $level == 'Acct' || $level == 'Days'){
			$total[0]['count(*)'] = count($list);
			if($arrParams['current'] == 1){
				$list = array_slice($list,0,$arrParams['pageSize']);
			}else{
				$startKey = ($arrParams['current'] - 1) * $arrParams['pageSize'];
				$list = array_slice($list,$startKey,$arrParams['pageSize']);
			}
		}
		$result = [
			'list' => $list,
			'pagination' => $arrParams,
			'curve' => $curve,
		];
		$result['pagination']['total'] = (int)$total[0]['count(*)'];
		$result['pagination']['current'] = (int)$result['pagination']['current'];
		$result['pagination']['pageSize'] = (int)$result['pagination']['pageSize'];
		
		return $result;
	}

	/* 获取广告主信息 */
	public function getAdsMasterInfo($accId){
		$where = array(
			'select' => 'company,contact_person',
			'where' => 'account_id='.$accId,
		);
		$res = $this->dbutil->getAccInfo($where);
		$res = array_values(array_filter(array_values($res[0])));
		
		return $res;
	}

	/* 处理查询数据 */
	public function handleReportData($level,$data){
		switch($level){
			case 'All':
				$res = array();
				$date = array();
				foreach($data as $k => $v){
					$accId = $v['account_id'];
					$res[$accId]['account_id'] = $accId;
					$res[$accId]['exposure_num'] += $v['exposure_num'];
					$res[$accId]['click_num'] += $v['click_num'];
					$res[$accId]['cpm'] += $v['cpm'];
					$res[$accId]['spend'] += $v['spend'];
					$date[$v['date']] = null;
				}

				foreach($res as $k => $v){
					$res[$k]['cpm'] = round($v['cpm'] / count($date),2);
					$res[$k]['adsMasterName'] = $this->getAdsMasterInfo($v['account_id'])[0];
					$res[$k]['click_rate'] = (intval($v['exposure_num']) == 0) ? 0 : round($v['click_num']/$v['exposure_num']*100, 2);
				}
				$res = array_values($res);
				break;
			case 'Acct':
				$res = array();
				$date = array();
				foreach($data as $k => $v){
					$proId = $v['pro_id'];
					$res[$proId]['pro_id'] = $proId;
					$res[$proId]['pro_name'] = $v['pro_name'];
					$res[$proId]['account_id'] = $v['account_id'];
					$res[$proId]['exposure_num'] += $v['exposure_num'];
					$res[$proId]['click_num'] += $v['click_num'];
					$res[$proId]['cpm'] += $v['cpm'];
					$res[$proId]['spend'] += $v['spend'];
					$date[$v['date']] = null;
				}

				foreach($res as $k => $v){
					$res[$k]['cpm'] = round($v['cpm'] / count($date),2);
					$res[$k]['adsMasterName'] = $this->getAdsMasterInfo($v['account_id'])[0];
					$res[$k]['click_rate'] = (intval($v['exposure_num']) == 0) ? 0 : round($v['click_num']/$v['exposure_num']*100, 2);
				}

				sort($res);
				break;
			case 'Ads':
				foreach($data as $k => $v){
					$data[$k]['adsMasterName'] = $this->getAdsMasterInfo($v['account_id'])[0];
					$data[$k]['date'] = date("Y-m-d",strtotime($v['date']));
					$data[$k]['click_rate'] = $v['click_rate'] * 100;
				}
				$res = $data;
				break;
			case 'Days':
				$res = array();
				$date = array();
				foreach($data as $k => $v){
					$time = date("Y-m-d",strtotime($v['date']));
					$res[$time]['date'] = $time;
					$res[$time]['account_id'] = $v['account_id'];
					$res[$time]['exposure_num'] += $v['exposure_num'];
					$res[$time]['click_num'] += $v['click_num'];
					$res[$time]['cpm'] += $v['cpm'];
					$res[$time]['spend'] += $v['spend'];
					$date[$time] = null;
				}
				foreach($res as $k => $v){
					$res[$k]['cpm'] = round($v['cpm'] / count($date),2);
					$res[$k]['adsMasterName'] = $this->getAdsMasterInfo($v['account_id'])[0];
					$res[$k]['click_rate'] = (intval($v['exposure_num']) == 0) ? 0 : round($v['click_num']/$v['exposure_num']*100, 2);
					$i++;
				}
				$res = array_values($res);
				break;
			case 'Curve':
				$res = array();
				$result = array();
				$date = array();
				foreach($data as $k => $v){
					$time = date("Y-m-d",strtotime($v['date']));
					$res[$time]['date'] = $time;
					$res[$time]['exposure_num'] += $v['exposure_num'];
					$res[$time]['click_num'] += $v['click_num'];
					$res[$time]['cpm'] += $v['cpm'];
					$res[$time]['spend'] += $v['spend'];
					$date[$time] = null;
				}

				$i = 0;
				foreach($res as $k => $v){
					$result['date'][$i] = $v['date'];
					$result['exposureNum'][$i] = $v['exposure_num'];
					$result['clickNum'][$i] = $v['click_num'];
					$result['clickRate'][$i] = (intval($v['exposure_num']) == 0) ? 0 : round($v['click_num']/$v['exposure_num']*100, 2);
					$result['cpm'][$i] = round($v['cpm'] / count($date),2);
					$result['spend'][$i] = $v['spend'];
					$i++;
				}
				$res = $result;
				break;
		}
		return $res;
	}
}

?>
