<?php
defined('BASEPATH') OR exit('No direct script access allowed');
	
/**
 * @后台登录
 */
class User extends BG_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model("UserModel");
	}

	/* 登录 */
	public function login(){
		$arrParams = json_decode(file_get_contents('php://input'), true);
		//$arrParams = $this->input->post(NULL,TRUE);
		if(!isset($arrParams['userName']) || empty($arrParams['userName'])){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'登录失败');
		}

		$res = $this->UserModel->doLogin($arrParams);
		if($res['status'] == 'ok'){
			return $this->outJson($res,ErrCode::OK,'登陆成功');
		}else{
			return $this->outJson($res,ErrCode::ERR_INVALID_PARAMS,'登陆失败');
		}
	}

	/* 检测登陆状态*/
	public function checkStatus(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}

		$res = $this->UserModel->checkLogin();
		return $this->outJson($res,ErrCode::OK, '登录成功');
	}

	/*退出登陆*/
	public function logout(){
		$res = $this->UserModel->clearLogin();
		if($res){
			return $this->outJson('',ErrCode::OK,'退出登陆');
		}else{
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS,'退出失败');
		}
	}
}
?>
