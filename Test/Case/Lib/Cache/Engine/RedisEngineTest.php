<?php
/**
 * MemcacheEngineTest file
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/view/1196/Testing>
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/view/1196/Testing CakePHP(tm) Tests
 * @package       Cake.Test.Case.Cache.Engine
 * @since         CakePHP(tm) v 1.2.0.5434
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Cache', 'Cache');
App::uses('RedisEngine', 'RedisCache.Cache/Engine');

/**
 * RedisEngineTest class
 *
 * @package       Redis
 */
class RedisEngineTest extends CakeTestCase {


    /**
     * Cache name used when testing
     *
     * @var string
     * @access private
     */
    private $_cache_name = 'redis_cache_test';


    /**
     * setUp method
     *
     * @return void
     */
    public function setUp() {
        $this->_cacheDisable = Configure::read('Cache.disable');
        Configure::write('Cache.disable', false);

        Cache::config($this->_cache_name, array(
            'engine' => 'RedisCache.Redis'
            , 'prefix' => $this->_cache_name . '_'
            , 'duration' => 1000
        ));
    }


    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown() {
        Configure::write('Cache.disable', $this->_cacheDisable);
        Cache::drop($this->_cache_name);
        Cache::config('default');
    }


    /**
     * Tests settings passed in from config get set correctly
     *
     * @return void
     */
    public function testSettings() {
        $settings = Cache::settings($this->_cache_name);
        unset($settings['serialize'], $settings['path'], $settings['groups']);
        $expecting = array(
            'engine' => 'RedisCache.Redis',
            'prefix' => $this->_cache_name . '_'
            , 'duration'=> 1000
            , 'probability' => 100
            , 
        );
        $this->assertSame($settings, $expecting);
    }


    /**
     * Tests read()/write() methods where data being read/written is a string
     */
    public function testReadWriteString() {
        Cache::set(array('duration' => 1), null, $this->_cache_name);

        $this->assertNull(Cache::read('test', $this->_cache_name));

        $data = 'this is a test of the emergency broadcasting system';
        $result = Cache::write('test', $data, $this->_cache_name);
        $this->__assertStatusOk($result);

        $result = Cache::read('test', $this->_cache_name);
        $expecting = $data; debug($result);
        $this->assertSame($result, $expecting);

        Cache::delete('test', $this->_cache_name);
    }


    /**
     * Tests read()/write() methods where data being read/written is a string zero
     */
    public function testReadWriteZeroString() {
        Cache::set(array('duration' => 1), null, $this->_cache_name);

        $this->assertNull(Cache::read('test', $this->_cache_name));

        $data = '0';
        $result = Cache::write('test', $data, $this->_cache_name);
        $this->__assertStatusOk($result);

        $result = Cache::read('test', $this->_cache_name);
        $expected = $data;
        $this->assertSame($expected, $result);

        Cache::delete('test', $this->_cache_name);
    }


    /**
     * Tests read()/write() methods where data being read/written is a positive integer
     */
    public function testReadWritePositiveInteger() {
        Cache::set(array('duration' => 1), null, $this->_cache_name);

        $this->assertNull(Cache::read('test', $this->_cache_name));

        $data = 20;
        $result = Cache::write('test', $data, $this->_cache_name);
        $this->__assertStatusOk($result);

        $result = Cache::read('test', $this->_cache_name);
        $expecting = $data;
        $this->assertSame($result, $expecting);

        Cache::delete('test', $this->_cache_name);
    }


    /**
     * Tests read()/write() methods where data being read/written is a negative integer
     */
    public function testReadWriteNegativeInteger() {
        Cache::set(array('duration' => 1), null, $this->_cache_name);

        $this->assertNull(Cache::read('test', $this->_cache_name));

        $data = -30;
        $result = Cache::write('test', $data, $this->_cache_name);
        $this->__assertStatusOk($result);

        $result = Cache::read('test', $this->_cache_name);
        $expecting = $data;
        $this->assertSame($result, $expecting);

        Cache::delete('test', $this->_cache_name);
    }


    /**
     * Tests read()/write() methods where data being read/written is an integer 0
     * gets returned by read as a string
     */
    public function testReadWriteIntegerZero() {
        Cache::set(array('duration' => 1), null, $this->_cache_name);

        $this->assertNull(Cache::read('test', $this->_cache_name));

        $data = 0;
        $result = Cache::write('test', $data, $this->_cache_name);
        $this->__assertStatusOk($result);

        $result = Cache::read('test', $this->_cache_name);
        $expected = (string) $data;
        $this->assertSame($expected, $result);

        Cache::delete('test', $this->_cache_name);
    }


    /**
     * Tests read()/write() methods where data being read/written is an array
     */
    public function testReadWriteArray() {
        Cache::set(array('duration' => 1), null, $this->_cache_name);

        $this->assertNull(Cache::read('test', $this->_cache_name));

        $data = array('bob' => 'says hi to you!');
        $result = Cache::write('test', $data, $this->_cache_name);
        $this->__assertStatusOk($result);

        $result = Cache::read('test', $this->_cache_name);
        $expecting = $data;
        $this->assertSame($result, $expecting);

        Cache::delete('test', $this->_cache_name);
    }


    /**
     * Tests read()/write() methods where data being read/written is an object
     */
    public function testReadWriteObject() {
        Cache::set(array('duration' => 1), null, $this->_cache_name);

        $this->assertNull(Cache::read('test', $this->_cache_name));

        $data = new StdClass();
        $data->testProperty = 'bob';
        $data->testArrayProperty = array('speech' => 'says Hi');

        $result = Cache::write('test', $data, $this->_cache_name);
        $this->__assertStatusOk($result);

        $result = Cache::read('test', $this->_cache_name);
        $expecting = $data;
        $this->assertEqual($result, $expecting);        // assertSame cannot be used as return is a different object, assertSame wants it to be the _same_ object...

        Cache::delete('test', $this->_cache_name);
    }


    /**
     * Tests can delete cache items
     */
	public function testDeleteCache() {
        Cache::set(array('duration' => 1), null, $this->_cache_name);

		$data = 'this is a test of the emergency broadcasting system';
		$result = Cache::write('delete_test', $data, $this->_cache_name);
		$this->__assertStatusOk($result);

        // assert data was written
        $result = Cache::read('delete_test', $this->_cache_name);
        $expecting = $data;
        $this->assertSame($result, $expecting);

		$result = Cache::delete('delete_test', $this->_cache_name);
		$this->assertTrue($result);

        // assert data was deleted
        $result = Cache::read('delete_test', $this->_cache_name);
        $this->assertNull($result);
	}

    /**
     * Tests can decrement numbers
     */
    public function testDecrement() {
        Cache::set(array('duration' => 1), null, $this->_cache_name);

        $result = Cache::write('test_decrement', 5, $this->_cache_name);
        $this->__assertStatusOk($result);

        $result = Cache::decrement('test_decrement', 1, $this->_cache_name);
        $this->assertSame(4, $result);

        $result = Cache::read('test_decrement', $this->_cache_name);
        $this->assertSame('4', $result);

        $result = Cache::decrement('test_decrement', 2, $this->_cache_name);
        $this->assertSame(2, $result);

        $result = Cache::read('test_decrement', $this->_cache_name);
        $this->assertSame('2', $result);
    }


    /**
     * Tests can increment numbers
     */
    public function testIncrement() {
        Cache::set(array('duration' => 1), null, $this->_cache_name);

        $result = Cache::write('test_increment', 5, $this->_cache_name);
        $this->__assertStatusOk($result);

        $result = Cache::increment('test_increment', 1, $this->_cache_name);
        $this->assertSame(6, $result);

        $result = Cache::read('test_increment', $this->_cache_name);
        $this->assertSame('6', $result);

        $result = Cache::increment('test_increment', 2, $this->_cache_name);
        $this->assertSame(8, $result);

        $result = Cache::read('test_increment', $this->_cache_name);
        $this->assertSame('8', $result);
    }

    /**
     * Asserts PRedis respose status is OK
     *
     * @param Predis\Response\Status $status
     * @return void
     */
    private function __assertStatusOk($status) {
        $this->assertTrue($status->getPayload() === 'OK');
    }
}