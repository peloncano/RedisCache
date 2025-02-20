<?php

/**
 * Redis Cache Class. Uses Predis as the PHP connection class
 */
class RedisEngine extends CacheEngine
{

    /**
     * Setting option name
     */
    const THROW_EXCEPTIONS = 'throwExceptions';

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
    public function init($settings = array())
    {

        if (!class_exists('Predis\Client')) {
            return false;
        }

        parent::init(
            array_merge(array(
                'engine' => 'Redis',
                'prefix' => Inflector::slug(APP_DIR) . '_',
                self::THROW_EXCEPTIONS => false
            ), $settings)
        );

        return $this->_connect();
    }

    /**
     * Connects to a Redis server
     *
     * @return bool True if Redis server was connected
     */
    protected function _connect()
    {
        try {

            if (!isset($this->_Predis)) {
                $this->_Predis = new Predis\Client(
                    RedisConfig::cacheConfig(),
                    array('prefix' => RedisConfig::$globalCachePrefix)
                );
            }
        } catch (Exception $e) {
            $this->__handleException($e, __FUNCTION__);
            return false;
        }

        return true;
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
    public function write($key = '', $data = null, $duration = 3600)
    {

        $data = $this->_compress($data);

        try {

            if (!$this->_Predis->isConnected()) {
                $this->_connect();
            }

            if ($duration === 0) {
                return $this->_Predis->set($key, $data);
            }

            return $this->_Predis->setex($key, $duration, $data);
        } catch (Exception $e) {
            $this->__handleException($e, __FUNCTION__);
            // Returning true here to trick Cake otherwise we would get a lot of 'unable to write' warnings
            // If this is not desire set self::THROW_EXCEPTIONS to true
            return true;
        }
    }

    /**
     * READ
     * Return whatever is stored in the key
     *
     * @param string $key
     * @access public
     * @return boolean
     */
    public function read($key = '')
    {
        try {
            $value = $this->_Predis->get($key);

            if (is_null($value)) {
                return false;
            }

            return $this->_expand($value);
        } catch (Exception $e) {
            $this->__handleException($e, __FUNCTION__);
            return false;
        }
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
    public function increment($key = '', $offset = 1)
    {
        try {
            return (int) $this->_Predis->incrby($key, $offset);
        } catch (Exception $e) {
            $this->__handleException($e, __FUNCTION__);
            return false;
        }
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
    public function decrement($key = '', $offset = 1)
    {
        try {
            return (int) $this->_Predis->decrby($key, $offset);
        } catch (Exception $e) {
            $this->__handleException($e, __FUNCTION__);
            return false;
        }
    }


    /**
     * DELETE
     * DEL the key from store
     *
     * @param string $key
     * @access public
     * @return boolean
     */
    public function delete($key = '')
    {
        try {
            // Predis::del returns an integer 1 on delete, convert to boolean
            return $this->_Predis->del($key) > 0;
        } catch (Exception $e) {
            $this->__handleException($e, __FUNCTION__);
            return false;
        }
    }


    /**
     * DEL all keys with settings[prefix]
     *
     * @param boolean $check
     * @access public
     * @return boolean
     */
    public function clear($check)
    {
        if ($check) {
            return true;
        }

        try {

            $keys = $this->_Predis->keys($this->settings['prefix'] . '*');

            if (!empty($keys)) {
                $this->_Predis->del($keys);
            }

            return true;
        } catch (Exception $e) {
            $this->__handleException($e, __FUNCTION__);
            return false;
        }
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
        try {
            foreach ($this->settings['groups'] as $group) {
                $value = $this->_Predis->get($this->settings['prefix'] . $group);
                if (!$value) {
                    $value = 1;
                    $this->_Predis->set($this->settings['prefix'] . $group, $value);
                }
                $result[] = $group . $value;
            }
        } catch (Exception $e) {
            $this->__handleException($e, __FUNCTION__);
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
        try {
            return (bool) $this->_Predis->incr($this->settings['prefix'] . $group);
        } catch (Exception $e) {
            $this->__handleException($e, __FUNCTION__);
            return false;
        }
    }


    /**
     * Disconnects from the redis server
     */
    public function __destruct()
    {
        if (empty($this->settings['persistent']) && $this->_Predis !== null) {
            $this->_Predis->disconnect();
        }
    }

    /**
     * Write data for key into cache if it doesn't exist already.
     * If it already exists, it fails and returns false.
     *
     * @param string $key Identifier for the data.
     * @param mixed $value Data to be cached.
     * @param int $duration How long to cache the data, in seconds.
     * @return bool True if the data was successfully cached, false on failure.
     */
    public function add($key, $value, $duration)
    {
        return true;
        if (!is_int($value)) {
            $value = serialize($value);
        }

        try {
            $result = $this->_Predis->setnx($key, $value);
            // setnx() doesn't have an expiry option, so overwrite the key with one
            if ($result) {
                return $this->_Predis->setex($key, $duration, $value);
            }
        } catch (Exception $e) {
            $this->__handleException($e, __FUNCTION__);
        }

        return false;
    }

    /**
     * GARBAGE COLLECTION - not required
     *
     * @access public
     * @return boolean
     */
    public function gc($expires = null)
    {
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
    private function _compress($data = null)
    {
        if (is_array($data) || is_object($data) || !preg_match('/^\d$/', $data)) {
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
    private function _expand($data = null)
    {
        if (false === $return = @unserialize($data)) {
            $return = $data;
        }

        return $return;
    }

    /**
     * This method will take care of handling exceptions by checking the settings self::THROW_EXCEPTIONS option
     * if set to true then it will rethrow the exception; otherwise it will just log it and continue
     *
     * @param Exception $e
     * @param string $metadata
     * @return void
     */
    private function __handleException(Exception $e, $metadata = '')
    {
        CakeLog::error('RedisCache.RedisEngine ' . $metadata . ' error ' . $e->getMessage());

        if ($this->settings[self::THROW_EXCEPTIONS]) {
            throw $e;
        }
    }
}
