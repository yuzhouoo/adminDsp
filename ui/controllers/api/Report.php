<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *@数据报表
 */
class Report extends BG_Controller{
	public function __construct(){
		parent::__construct();

		$this->load->model("ReportModel");
	}

	public function reportData(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}

		$arrParams = $this->input->get(NULL,TRUE);
		$arrParams['current'] = (isset($arrParams['current']) && !empty($arrParams['current'])) ? $arrParams['current'] : 1;
		$arrParams['pageSize'] = (isset($arrParams['pageSize']) && !empty($arrParams['pageSize'])) ? $arrParams['pageSize'] : 20;

		$res = $this->ReportModel->getReportData($arrParams);
		if(!$res['list']){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'数据查询失败');
		}
		
		return $this->outJson($res,ErrCode::OK,'查询数据成功');
	}


}
?>
