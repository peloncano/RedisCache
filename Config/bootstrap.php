<?php
/**
 * Config settings to connect to the Redis server.
 * There are different configs for servers performing
 * different tasks to allow each server to be configured
 * to it's specific task
 */
class RedisConfig {

    static public $default = array(
        'cache' => array(
            'hostname' => '127.0.0.1'
            , 'port' => 6379
            , 'password' => null
        )
        , 'session' => array(
            'hostname' => '127.0.0.1'
            , 'port' => 6379
            , 'password' => null
        )
    );
}

// Redis plugin depends on Predis
// @link https://github.com/nrk/predis
App::import('Vendor', 'predis/predis/autoload');
// Predis\Autoloader::register();/