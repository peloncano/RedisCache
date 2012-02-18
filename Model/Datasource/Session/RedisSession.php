<?php

App::uses('CakeSessionHandlerInterface', 'Model/Datasource/Session');

/**
 * Redis Session Store Class. Uses Predis as the PHP connection class
 */
class RedisSession implements CakeSessionHandlerInterface {


    /**
     * Placeholder for cached Redis resource
     *
     * @var object
     * @access public
     */
    public $_Predis;


    /**
     * Seconds until key should expire
     *
     * @var int
     * @access public
     */
    public $timeout;


    /**
     * Prefix to apply to all Redis session keys
     *
     * @var string
     * @access public
     */
    public $prefix;


    /**
      * OPEN
      * - Connect to Redis
      * - Calculate and set timeout for SETEX
      * - Set session_name as key prefix
      *
      * @access public
      * @return boolean true
      */
    public function open() {
        $name = Configure::read('Session.cookie');
        $timeout = Configure::read('Session.timeout');

        $this->timeout = $timeout * Security::inactiveMins();
        $this->prefix = $name;

        $this->_Predis = new Predis\Client(
            RedisConfig::$default['session']
            , array('prefix' => $this->prefix)
        );

        return true;
    }


    /**
     * CLOSE
     * - Disconnect from Redis
     *
     * @access public
     * @return boolean true
     */
    public function close() {
        $this->_Predis->disconnect();
        return true;
    }


    /**
     * READ
     * - Return whatever is stored in session ID (as key)
     * - Session ID is autoprefixed by Predis to create the key
     *
     * @param string $session_id
     * @access public
     * @return boolean
     */
    public function read( $session_id = '' ) {
        return $this->_Predis->get($session_id);
    }


    /**
     * WRITE
     * - SETEX data with timeout calculated in open()
     * - Session ID is autoprefixed by Predis to create the key
     *
     * @param string $session_id
     * @param mixed $data
     * @access public
     * @return boolean
     */
    public function write( $session_id = '', $data = null ) {
        if($session_id && is_string($session_id)) {
            return $this->_Predis->setex($session_id, $this->timeout, $data);
        }

        return false;
    }


    /**
     * DESTROY
     * - DEL the key from store
     * - Session ID is autoprefixed by Predis to create the key
     *
     * @param string $session_id
     * @access public
     * @return boolean
     */
    public function destroy( $session_id = '' ) {
        // Predis::del returns an integer 1 on delete, convert to boolean
        return $this->_Predis->del($session_id) ? true : false;
    }


    /**
     * GARBAGE COLLECTION
     * not needed as SETEX automatically removes itself after timeout
     * ie. works like a cookie
     *
     * @param int $expires defaults to null
     * @access public
     * @return boolean
     */
    public function gc( $expires = null ) {
        return true;
    }
}