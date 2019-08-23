<?php

/**
 * Redis Cache Class. Uses Predis as the PHP connection class
 */
class RedisEngine extends CacheEngine {


    /**
     * Settings
     *
     * @var array
     */
    public $settings = array();

    /**
     * Predis wrapper.
     *
     * @var _Predis
     */
    protected $_Predis = null;

    /**
     * Initialize the Redis Cache Engine
     *
     * Called automatically by the cache frontend
     * To reinitialize the settings call Cache::engine('EngineName', [optional] settings = array());
     *
     * @param array $settings array of setting for the engine
     * @return boolean true
     */
    public function init( $settings = array() ) {

        if (!class_exists('Predis\Client')) {
            return false;
        }

        parent::init(array_merge(array(
            'engine'=> 'Redis'
            , 'prefix' => Inflector::slug(APP_DIR) . '_'
            ), $settings)
        );

        if ( ! isset($this->_Predis)) {
            $this->_Predis = new Predis\Client(
                RedisConfig::cacheConfig()
                , array('prefix' => $this->settings['prefix'])
            );
        }

        return true;
    }


    /**
     * READ
     * Return whatever is stored in the key
     *
     * @param string $key
     * @access public
     * @return boolean
     */
    public function read( $key = '' ) {
        return $this->_expand($this->_Predis->get($key));
    }


    /**
     * WRITE
     * SET if no duration passed in else SETEX
     *
     * @param string $key
     * @param mixed $data
     * @param integer $duration defaults to 3600 (seconds)
     * @access public
     * @return boolean
     */
    public function write( $key = '', $data = null, $duration = 3600 ) {
        return $this->_Predis->setex($key, $duration, $this->_compress($data));
    }


    /**
     * INCREMENT
     * Increase the value of a stored key by $offset
     *
     * @param string $key
     * @param integer $offset
     * @access public
     * @return boolean
     */
    public function increment( $key = '', $offset = 1 ) {
        return $this->_Predis->incrby($key, $offset);
    }


    /**
     * DECREMENT
     * Reduce the value of a stored key by $offset
     *
     * @param string $key
     * @param integer $offset
     * @access public
     * @return boolean
     */
    public function decrement( $key = '', $offset = 1 ) {
        return $this->_Predis->decrby($key, $offset);
    }


    /**
     * DELETE
     * DEL the key from store
     *
     * @param string $key
     * @access public
     * @return boolean
     */
    public function delete( $key = '' ) {
        // Predis::del returns an integer 1 on delete, convert to boolean
        return $this->_Predis->del($key) ? true : false;
    }


    /**
     * CLEAR - not implemented
     * DEL all keys with settings[prefix]
     *
     * @param boolean $check
     * @access public
     * @return boolean
     */
    public function clear( $check ) {
        return true;
    }


    /**
     * GARBAGE COLLECTION - not required
     *
     * @access public
     * @return boolean
     */
    public function gc($expires = null) {
        return true;
    }


    /**
     * Compress $data as redis stores strings.
     * Do not compress numeric data as compressed numbers cannot be used for incr/decr operations
     *
     * @param null $data
     * @return null|string
     * @access private
     */
    private function _compress( $data = null ) {
        if(is_array($data) || is_object($data) || ! preg_match('/^\d$/', $data)) {
            $return = serialize($data);
        } else {
            $return = $data;
        }

        return $return;
    }


    /**
     * Decompress stored data
     *
     * @param null $data
     * @return mixed
     * @access private
     */
    private function _expand( $data = null ) {
        if(false === $return = @unserialize($data)) {
            $return = $data;
        }

        return $return;
    }

    /**
     * Returns the `group value` for each of the configured groups
     * If the group initial value was not found, then it initializes
     * the group accordingly.
     *
     * @return array
     */
    public function groups()
    {
        $result = array();
        foreach ($this->settings['groups'] as $group) {
            $value = $this->_Predis->get($this->settings['prefix'] . $group);
            if (!$value) {
                $value = 1;
                $this->_Predis->set($this->settings['prefix'] . $group, $value);
            }
            $result[] = $group . $value;
        }
        return $result;
    }

    /**
     * Increments the group value to simulate deletion of all keys under a group
     * old values will remain in storage until they expire.
     *
     * @param string $group The group name to clear.
     * @return bool success
     */
    public function clearGroup($group)
    {
        return (bool) $this->_Predis->incr($this->settings['prefix'] . $group);
    }


    /**
     * Disconnects from the redis server
     */
    public function __destruct()
    {
        if (empty($this->settings['persistent']) && $this->_Predis !== null) {
            $this->_Predis->close();
        }
    }
}