<?php
/**
 * 自定义Controller基类
 */
class BG_Controller extends CI_Controller {

	public $arrUser = [];

	public function __construct() {
		parent::__construct();
		$this->load->model('UserModel');
		$this->arrUser = $this->UserModel->checkLogin();
	}


	/**
	 *json 输出
	 *
	 * @param $array
	 * @bool $bolJsonpSwitch
	 */
	protected function outJson($arrData, $intErrCode, $strErrMsg=null,$bolJsonpSwitch = false) {
		header("Content-Type:application/json");
		$arrData = ErrCode::format($arrData, $intErrCode, $strErrMsg);
		echo json_encode($arrData);
	}

}
?>
