<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *@投放的所有广告
 */
class Ads extends BG_Controller{
	public function __construct(){
		parent::__construct();
		
		$this->load->model("AdsModel");
	}

	public function adsList(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}

		$arrParams = $this->input->get(NULL,TRUE);
		$arrParams['current'] = isset($arrParams['current']) && !empty($arrParams['current']) ? $arrParams['current'] : '1';
		$arrParams['pageSize'] =  isset($arrParams['pageSize']) && !empty($arrParams['pageSize']) ?  $arrParams['pageSize'] : '20';
		
		$res = $this->AdsModel->getList($arrParams);
		if($res){
			return $this->outJson($res,ErrCode::OK,'查询数据成功');
		}else{
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'数据查询失败');
		}
	}

	public function adsInfo(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}

		$arrParams = $this->input->get(NULL,TRUE);
		$res = $this->AdsModel->getInfo($arrParams);
		if($res){
			return $this->outJson($res,ErrCode::OK,'查询数据成功');
		}else{
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'数据查询失败');
		}
	}

	public function adsCheck(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}
		$arrParams = file_get_contents('php://input');
		$arrParams = json_decode($arrParams,true);
		
		$res = $this->AdsModel->modifyAdsStatus($arrParams);
		if(isset($arrParams['running_status']) && !empty($arrParams['running_status'])){
			$info = $this->AdsModel->getList($arrParams);
		}else{
			$info = $this->AdsModel->getInfo($arrParams);
		}

		if($res){
			return $this->outJson($info,ErrCode::OK,'审核操作成功');
		}
		
		return $this->outJson($info,ErrCode::ERR_INVALID_PARAMS,'审核操作失败');
	}
}
?>
