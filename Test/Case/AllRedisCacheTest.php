<?php
/**
 * All RedisCache plugin tests
 */
class AllRedisCacheTest extends CakeTestCase {

/**
 * Suite define the tests for this plugin
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All RedisCache test');

		$path = CakePlugin::path('RedisCache') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
