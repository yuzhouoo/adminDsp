<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 账户信息 
 */

class UploadTools {

    
    const KEY_IMG_URL_SALT = 'Qspjv5$E@Vkj7fZb';

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->config->load('upload');
        $this->CI->load->helper(['form', 'url']);
    }

    public function index() {
        $this->CI->load->view('upload_form', ['error' => '']);
    }

	/**
     *
	 */
	public function upload($arrUdpConf) {
        $arrUdpConf['upload_path'] = $arrUdpConf['upload_path'] . date("Ym") . '/';

        if (!$this->makeDir($arrUdpConf['upload_path'])) {
            return '';
        }
        $this->CI->load->library('upload', $arrUdpConf);

        if (!$this->CI->upload->do_upload('file')) {
            return '';
        }
        $arrRes = $this->CI->upload->data();

        $strUrl = explode('web/', $arrRes['full_path'])[1];
        return $strUrl;
    }

    /**
     * 递归检测、创建文件夹
     * @param string $strConfDir upload/txt
     * @return bool
     */
    private function makeDir($strConfDir){
        if (is_dir(FCPATH . $strConfDir)) {
            return true;
        } else if (@mkdir(FCPATH . $strConfDir,0777)) {
            return true;
        } else {
            $arrConfDir = explode('/',$strConfDir);
            $dirTmp = '';
            foreach ($arrConfDir as $dir) {
                if (empty($dir)) {
                    continue;
                }
                $dirTmp .= $dir . '/';
                if (!$this->makeDir($dirTmp)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
