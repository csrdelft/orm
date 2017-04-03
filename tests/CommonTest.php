<?php
use CsrDelft\Orm\Util;
use PHPUnit\Framework\TestCase;

/**
 * CommonTest.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
final class CommonTest extends TestCase {
	public function testStartsWith() {
		$this->assertTrue(\common\starts_with("mystring", "my"));
		$this->assertTrue(\common\starts_with("mystring", ""));
		$this->assertFalse(\common\starts_with("mystring", "string"));
	}

	public function testEndsWith() {
		$this->assertTrue(\common\ends_with("mystring", "string"));
		$this->assertTrue(\common\ends_with("mystring", ""));
		$this->assertFalse(\common\ends_with("mystring", "my"));
	}

	public function testPdoBool() {
		$this->assertEquals("string", \common\pdo_bool("string"));
		$this->assertEquals(0, \common\pdo_bool(0));
		$this->assertEquals(0, \common\pdo_bool(false));
		$this->assertEquals(1, \common\pdo_bool(true));
	}


}
