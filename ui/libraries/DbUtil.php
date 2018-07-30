<?php
/**
 * getXXX($arrParams)
 * $arrParams = [
 *     'select' => 'username,passwd',  // 'count(*)' 查询总数
 *     'where' => 'create_time>0 AND update_time>0',
 *     'order_by' => 'passwd DESC',
 *     'limit' => '0,1',
 * ];
 *
 * setXXX($arrParams)
 * $arrParams = [
 *     'username' => 'aaa',
 *     'phone' => 'bbb',
 *     ... ... 
 * ];
 *
 * udpXXX($arrParams)
 * $arrParams = [
 *     'email' => 'xxx',
 *     'name' => 'xxx',
 *     ...
 *     'where' => 'account_id=1 and app_id=1',
 * ];
 *
 * sqlTrans($arrParams);
 * $arrParams = array(
 *	0 => array (
 *		'type' => '操作类型 insert,update,delete',
 *		'tabName' => '表名',
 *		'where' => 'update和delete操作',
 *		'data' => array(
 *			'表字段' => 'value',
 *			...
 *		),
 *	),
 *	1 => array(
 *	...
 *	),
 *
 * ),
 */
class DbUtil {

	const TAB_BG_USER				= 'bg_user';
	const TAB_ACC_INFO				= 'dsp_accountinfo';
	const TAB_PRO_INFO				= 'dsp_proinfo';
	const TAB_PRO_DATA				= 'dsp_prodata';
	const TAB_PRO_SUMMARY			= 'dsp_prosummary';
	const TAB_APK_MANAGE			= 'dsp_apk';

	const TAB_MAP = [
		'bguser' => self::TAB_BG_USER,
		'accinfo' => self::TAB_ACC_INFO,
		'proinfo' => self::TAB_PRO_INFO,
		'prodata' => self::TAB_PRO_DATA,
		'prosummary' => self::TAB_PRO_SUMMARY,
		'apkmanage' => self::TAB_APK_MANAGE,
	];

    public static $instance;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /**
     * @param string $strFuncName
     * @param array $arrParams
     * @return array
     */
    public function __call($strFuncName, $arrParams = []) {
        $strTabName = preg_match('#(get|set|udp|del|getall)([A-Z].*)#', $strFuncName, $arrAcT);
        if (empty($arrAcT[1])
            || empty($arrAcT[2])
            || (!in_array(strtolower($arrAcT[2]), array_keys(self::TAB_MAP)))) {
            throw new Exception('DbUtil has no [method|table] : [' . $arrAcT[1] . ']|[' . $arrAcT[2] . ']');
        }

        if ($arrAcT[1] === 'get'
            || $arrAcT[1] === 'set') {
            ;
        }
        return $this->{$arrAcT[1]}(self::TAB_MAP[strtolower($arrAcT[2])], $arrParams[0]);
    }

    /**
     *
     */
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new DbUtil();
        }
        return self::$instance;
    }

    /**
     *
     */
    public function mysqlEscape(&$strParam) {
        $this->CI->db->escape_str($strParam);
    }

    /**
     * @param string $strTabName
     * @param array $arrParams
     * @return array
     */
    private function get($strTabName, $arrParams) {
        foreach ($arrParams as $act => $sqlPart) {
            if ($act === 'limit') {
                $arrLimit = explode(',', $sqlPart);
                // ci limit 参数和 sql 相反
                $this->CI->db->limit($arrLimit[1], $arrLimit[0]);
				continue;
			}
            $this->CI->db->$act($sqlPart);
        }
        $objRes = $this->CI->db->get($strTabName);
		//echo $this->CI->db->last_query();
		if (empty($objRes)) {
            return [];
        }
        $arrRes = $objRes->result_array();
        if (!empty($arrRes[0])) {
            return $arrRes;
        }
        return $arrRes;
    }

    /**
     * @param string $strTabName
     * @param array $arrParams
     * @return array
     */
    private function getall($strTabName, $arrParams) {
        foreach ($arrParams as $act => &$sqlPart) {
            $this->CI->db->$act($sqlPart);
        }
        $objRes = $this->CI->db->get($strTabName);
		if (empty($objRes)) {
            return [];
        }
        $arrRes = $objRes->result_array();
        if (!empty($arrRes[0])) {
            return $arrRes;
        }
        return $arrRes;
    }

    /**
     * @param string $strTabName
     * @param array $arrParams
     * @return bool
     */
    private function set($strTabName, $arrParams) {
        $arrParams['create_time'] = time();
        $arrParams['update_time'] = time();
        $this->CI->db->insert($strTabName, $arrParams);
        $arrRes = $this->CI->db->error();
        $arrRes['message'] = $this->formatErrMessage($arrRes);
        return $arrRes;
    }

    /**
     *
     */
    private function udp($strTabName, $arrParams) {
        $arrParams['update_time'] = time();
        $strWhere = $arrParams['where'];
        unset($arrParams['where']);
        foreach ($arrParams as $key => $val) {
            $this->CI->db->set($key, $val);
        }
        $this->CI->db->where($strWhere);
        $this->CI->db->update($strTabName);
        if ($this->CI->db->affected_rows() === 0) {
            return [
                'code' => '-1',
                'message' => 'affected rows 0',
            ];
        }
        $arrRes = $this->CI->db->error();
        return $arrRes;
    }

    /**
     * del
     */
    private function del($strTabName, $arrParams) {
        $strWhere = $arrParams['where'];
        $this->CI->db->where($strWhere);
        $this->CI->db->delete($strTabName);
        $arrRes = $this->CI->db->error();
        return $arrRes;
    }

    /**
     * @param array $arrRes
     * @return string
     */
    private function formatErrMessage($arrRes) {
        $strPattern = '#\'(.*)\'#';
        switch ($arrRes['code']) {
            case 1062:
                preg_match($strPattern, $arrRes['message'], $arrOut);
                return $arrOut[1];
            default:
                return '';
        }
    }

    /**
     * @param string $strSql
     * @return array
     */
    public function query($strSql) {
        $res = $this->CI->db->query($strSql);
        if (is_bool($res)) {
            return $res;
        }
        $res = $objRes->result_array();
        return $arrRes;
    }

    /**
     * @param string $strTabKey
     * @return string
     */
    public function getAutoincrementId($strTabKey) {
        $strTabName = self::TAB_MAP[$strTabKey];
        $strSql = "SELECT AUTO_INCREMENT FROM information_schema.tables where table_name='$strTabName'";
        $objRes = $this->CI->db->query($strSql);
        $arrRes = $objRes->result_array();
        if (empty($arrRes)) {
            return 0;
        }
        return intval($arrRes[0]['AUTO_INCREMENT']);
	}

	/*
	 * CI手动事务，临时禁用自动事务
	 * @params array $arrParams
	 * return bool true OR false
	 * 注：由于ci事务判断出错回滚的条件是语句是否执行成功，而更新操作时，就算影响的条数为0，sql语句执行的结果过仍然为1，因为它执行成功了，只是影响的条数为0。
	 */
	public function sqlTrans($arrParams){
		if(empty($arrParams)){
			return [];
		}

		$this->CI->db->trans_strict(FALSE);
		$this->CI->db->trans_begin();
		foreach($arrParams as $key => $value){
			$type = $value['type'];
			$TabName = self::TAB_MAP[strtolower($value['tabName'])];

			switch($type){
				case 'insert':
					$this->CI->db->insert($TabName,$value['data']);
					break;
				case 'update':
					$this->CI->db->where($value['where']);
					$this->CI->db->update($TabName,$value['data']);
                    if ($this->CI->db->affected_rows() === 0) {
						$this->CI->db->trans_rollback();
			            return false;
					}
					break;
				case 'delete':
					$this->CI->db->where($value['where']);
					$this->CI->db->delete($TabName);
					
                    if ($this->CI->db->affected_rows() === 0) {
						$this->CI->db->trans_rollback();
			            return false;
					}
					break;
			}
		}

		/* 事务回滚和提交*/
		if ($this->CI->db->trans_status() === FALSE){
			//@todo 事务回滚 异常处理部分
			$this->CI->db->trans_rollback();
			return false;
		}else{
			//@todo 事务提交
			$this->CI->db->trans_commit();
			return true;
		}
	}
}
