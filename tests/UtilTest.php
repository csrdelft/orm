<?php
use CsrDelft\Orm\Util;
use PHPUnit\Framework\TestCase;

/**
 * UtilTest.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * @covers CsrDelft\Orm\Util
 */
final class UtilTest extends TestCase {
	public function testStartsWith() {
		$this->assertTrue(Util::starts_with("mystring", "my"));
		$this->assertTrue(Util::starts_with("mystring", ""));
		$this->assertFalse(Util::starts_with("mystring", "string"));
	}

	public function testEndsWith() {
		$this->assertTrue(Util::ends_with("mystring", "string"));
		$this->assertTrue(Util::ends_with("mystring", ""));
		$this->assertFalse(Util::ends_with("mystring", "my"));
	}

	public function testPdoBool() {
		$this->assertEquals("string", Util::pdo_bool("string"));
		$this->assertEquals(0, Util::pdo_bool(0));
		$this->assertEquals(0, Util::pdo_bool(false));
		$this->assertEquals(1, Util::pdo_bool(true));
	}


}
