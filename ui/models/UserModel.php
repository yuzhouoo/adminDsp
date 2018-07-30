<?php
/**
 * @登录model
 */
class UserModel extends CI_Model{
	const EXPIRE_SESSION = 3600;

	public function __construct(){
		parent::__construct();
		session_start();
	}

	public function doLogin($arrParams){
		$where = array(
			'select' => '*',
			'where' => 'username="'.$arrParams['userName'].'" AND password="'.md5($arrParams['passWord']).'"',
		);

		$this->load->library('DbUtil');
		$res = $this->dbutil->getBgUser($where);

		/*没有账号*/
		if(!$res){
			return [
				'status' => 'error',
				'type' => $arrParams['type'],
			];
		}

		$_SESSION['bg_login_time'] = time();
		$_SESSION['account_id'] = $res[0]['id'];
		$_SESSION['bg_name'] = $res[0]['username'];

		/** 登录成功 */
		return [
			'status' => 'ok',
			'type' => $arrParams['type'],
			'currentAuthority' => (string)1,
			'bg_name' => $_SESSION['bg_name'],
			'bg_avatar' => 'https://gw.alipayobjects.com/zos/rmsportal/BiazfanxmamNRoxxVxka.png',
		];
	}

	public function checkLogin(){
		if (isset($_SESSION['bg_login_time'])
			&& isset($_SESSION['account_id'])
			&& isset($_SESSION['bg_name'])
			&& (time() - $_SESSION['bg_login_time']) <= self::EXPIRE_SESSION) {
			/* 更新session时间 */
			$_SESSION['bg_login_time'] = time();

			return [
				'account_id' => $_SESSION['account_id'],
				'bg_name' => $_SESSION['bg_name'],
				'bg_avatar' => 'https://gw.alipayobjects.com/zos/rmsportal/BiazfanxmamNRoxxVxka.png',
			];
		}
		return [];
	}

	public function clearLogin(){
		setcookie('BGXDL_DSP', '', time()-1, '/');
		$_SESSION = [];
		return true;
	}
}
?>
