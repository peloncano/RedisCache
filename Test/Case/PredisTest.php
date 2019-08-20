<?php

/**
 * @author Ian Hill
 * @copyright 2008
 */


/**
 * PredisTest class
 *
 * @package              Plugin.Redis
 * @subpackage           Plugin.Redis.Case
 */
class PredisTestCase extends CakeTestCase {


	/**
	 * Tests Predis class is available having been imported in the plugin bootstrap
	 */
	public function testPredisIsAvailable(){
        $this->assertTrue(class_exists('Predis\Client'));

        $predis = new Predis\Client(RedisConfig::$default['session']);
        $this->assertIsA($predis, 'Predis\Client');
        $predis->disconnect();
    }


	/**
	 * Tests Redis 'Session' server can be used via Predis
     * We test setting, retrieving and deleting a key/value
	 */
    function testPredisConnectionToSessionServer(){
        $predis = new Predis\Client(RedisConfig::$default['session'], array('prefix' => 'test_'));

        // Use a random value to avoid the possibility of a non-deleted key
        // making it appear like writes are succeeding
        $rand = rand();

        $setTestKeyResponse = $predis->set('test_key', $rand);

        $this->assertEquals('OK', $setTestKeyResponse->getPayload());
        $this->assertEquals($rand, $predis->get('test_key'));

        $predis->del('test_key');
        $this->assertIdentical(null, $predis->get('test_key'));

        //cleanup
        if(array() != $keys = $predis->keys('*')) {
            foreach($keys as $key) {
                if(strpos($key, 'test_') !== false) {
                    $predis->del($key);
                }
            }
        }

        $predis->disconnect();
    }
}
?>