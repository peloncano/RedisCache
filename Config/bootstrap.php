<?php
/**
 * Config settings to connect to the Redis server.
 * There are different configs for servers performing
 * different tasks to allow each server to be configured
 * to it's specific task
 */
class RedisConfig {

    static public $default = [
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' =>  null
        ];

    /**
     * String that will be added as a prefix in the redis cache client
     *
     * @var string
     */
    static public $globalCachePrefix = '';
    
    /**
     * String that will be added as a prefix in the redis session client
     *
     * @var string
     */
    static public $globalSessionPrefix = '';

    public static function cacheConfig() {

        $customConfiguration = (array)Configure::read('RedisCache.cache');

        return array_merge(
            self::$default,
            $customConfiguration
        );
    }

    public static function sessionConfig() {

        $customConfiguration = (array) Configure::read('RedisCache.session');

        return array_merge(
            self::$default,
            $customConfiguration
        );
    }
}

// Redis plugin depends on Predis
// @link https://github.com/nrk/predis
App::import('Vendor', 'predis/predis/autoload');
// Predis\Autoloader::register();/