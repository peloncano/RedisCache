# RedisCache CakePHP 2.x Plugin
A CakePHP 2.x plugin enabling [Redis](http://redis.io/) backed sessions and caching. Uses the [Predis](https://github.com/nrk/predis) library to communicate with Redis.

> Had to resurrect this plugin for a legacy project that needed to connect to redis via TLS/SSL.

## Installation

Run composer command

```
composer require peloncano/cakephp-plugin-redis-cache
```	

## Initial setup required

The plugin includes its own `bootstrap.php` file which must be loaded in the application `app/Config/bootstrap.php` file like so:

```
CakePlugin::load(array('RedisCache' => array('bootstrap' => true)));
```

The plugin will, by default, communicate with a Redis server setup on `localhost` at port `6379` with no password. However
you may use remote Redis servers if desired. You can also configure different servers for caching and session duties
as the exact configuration of these may be slightly different for best performance.


## Using for Caching:

### Connection configuration

if loading a configuration file ([Doc](https://book.cakephp.org/2.0/en/development/configuration.html#loading-configuration-files)):

```
$config['RedisCache'] = array(
    'cache' => array(
        'password' => '',
        'port' => 6379,
        'database' => 0
    )
);
```

If TLS/SSL is needed:

```
$config['RedisCache'] = array(
    'cache' => array(
        'scheme' => 'tls',
        'ssl' => ['verify_peer' => false], // any other predis SSL related options here
        'host' => 'localhost',
        'password' => '',
        'port' => 6379, // could be different for tls connections
        'database' => 0
    )
);
```


In `app/Config/bootstrap.php` you can now configure your cache
> Note: Make sure this is done after the loading of the plugin

```
Cache::config('default', array('engine' => 'RedisCache.Redis'));

// with prefix and duration
Cache::config('other_cache_with_prefix', array(
		'engine' => 'RedisCache.Redis', 
		'prefix' => 'other_cache_with_prefix', 
		'duration'=> '+1 week'
));
	
// `throwExceptions` set to TRUE will throw exceptions on redis connection issues
// by default this is FALSE which will allow the app to continue working if caching fails
// due to redis/predis connection issues
Cache::config('other_cache', array(
		'engine' => 'RedisCache.Redis', 
		'throwExceptions' => true
));
```


## Using for Session handling (PENDING):

In `app/Config/core.php` under the session configuration section:

```
Configure::write('Session', array(
        'cookie' => {{YOUR COOKIE NAME HERE}}
        , 'timeout' => 60
        , 'handler' => array(
            'engine' => 'RedisCache.RedisSession'
        )
	));
```
	
## Global Prefix
You can add a global prefix to namespace all keys by adding the following lines right after the plugin is loaded.

For `Cache`:

```
RedisConfig::$globalCachePrefix = Inflector::slug('my app cache prefix') . ':'; // use ':' to group keys in redis
```

For `Session`:

```
RedisConfig::$globalSessionPrefix= Inflector::slug('my app session prefix') . ':'; // use ':' to group keys in redis
```

> Note: This would apply before any cache specific prefix