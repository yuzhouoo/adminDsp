<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *@广告主管理
 */
class AdsMaster extends BG_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->model('AdsMasterModel');
	}

	public function masterList(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}

		$arrParams = $this->input->get(NULL,TRUE);
		$arrParams['current'] = (isset($arrParams['current']) && !empty($arrParams['current'])) ? $arrParams['current'] : 1;
		$arrParams['pageSize'] = (isset($arrParams['pageSize']) && !empty($arrParams['pageSize'])) ? $arrParams['pageSize'] : 20;

		$res = $this->AdsMasterModel->getList($arrParams);
		if($res){
			return $this->outJson($res,ErrCode::OK,'渠道列表查询成功');
		}else{
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'渠道列表查询失败');
		}
	}

	public function masterInfo(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}

		$arrParams = $this->input->get(NULL,TRUE);	
		if(!isset($arrParams['account_id']) || empty($arrParams['account_id'])){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数错误,信息查询失败');
		}

		$res = $this->AdsMasterModel->getMasterInfo($arrParams['account_id']);
		if($res){
			return $this->outJson($res,ErrCode::OK,'用户信息查询成功');
		}else{
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'用户信息查询失败');
		}
	}

	public function masterCheck(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}

		$arrParams = file_get_contents('php://input');
		$arrParams = json_decode($arrParams,true);

		$res = $this->AdsMasterModel->modifyMasterStatus($arrParams);
		$Info = $this->AdsMasterModel->getMasterInfo($arrParams['account_id']);
		if($res){
			return $this->outJson($Info,ErrCode::OK,'用户审核成功');
		}else{
			return $this->outJson($Info,ErrCode::ERR_INVALID_PARAMS,'用户审核失败,可能已经审核');
		}
	}

	public function authMasterInfo(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}

		$arrParams = $this->input->get(NULL,TRUE);
		if(empty($arrParams['account_id'])){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数有误');
		}

		if($arrParams['check_status'] == '3' && empty($arrParams['check_msg'])){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'未填写审核失败原因');
		}

		$res = $this->AdsMasterModel->modifyMasterStatus($arrParams);
		if($res){
			return $this->outJson($res,ErrCode::OK,'用户审核成功');
		}else{
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'审核失败,该用户已审核');
		}
	}
}
?>
