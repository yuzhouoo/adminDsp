<?php
/**
 *
 */
class RedisUtil extends Redis {
    
    private static $instance = null;

    /**
     *
     */
    public function __construct() {
        parent::__construct();
        $CI =& get_instance();
		$CI->config->load('redis',true);
		$arrRedisConf = $CI->config->item('redis');
		$this->connect($arrRedisConf['redis'][0]['server'], $arrRedisConf['redis'][0]['port']);
	} 

    /**
     *
     */
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new RedisUtil();
        }
        return self::$instance;
    }
}
