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
		$this->assertTrue(starts_with("mystring", "my"));
		$this->assertTrue(starts_with("mystring", ""));
		$this->assertFalse(starts_with("mystring", "string"));
	}

	public function testEndsWith() {
		$this->assertTrue(ends_with("mystring", "string"));
		$this->assertTrue(ends_with("mystring", ""));
		$this->assertFalse(ends_with("mystring", "my"));
	}

	public function testPdoBool() {
		$this->assertEquals("string", pdo_bool("string"));
		$this->assertEquals(0, pdo_bool(0));
		$this->assertEquals(0, pdo_bool(false));
		$this->assertEquals(1, pdo_bool(true));
	}


}
