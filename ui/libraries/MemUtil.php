<?php
/**
 *
 */
class MemUtil {
    
    private static $instance = null;
    protected $objMemcached = null;

    /**
     *
     */
    public function __construct() {
        $this->objMemcached = new Memcached();
        $CI =& get_instance();
        $CI->config->load('memcached', true);
        $arrMemcachedConf = $CI->config->item('memcached');
        $this->objMemcached->connect($arrMemcachedConf['memcached'][0]['server'], $arrMemcachedConf['memcached'][0]['port']);
    } 

    /**
     *
     */
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new MemcachedUtil();
        }
        return self::$instance;
    }

    /**
     * @param string $key
     * @param string|array $val
     * @param int $expire
     * @retrun bool
     */
    public function set($key, $val, $expire = 60) {
        return $this->objMemcached->set($key, $val, $expire);
    } 

    /**
     *
     */
    public function get($key) {
        return $this->objMemcached->set($key);
    }
}
