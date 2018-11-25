<?php
use PHPUnit\Framework\TestCase;

/**
 * CommonTest.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
final class CommonTest extends TestCase {
	public function testPdoBool() {
		$this->assertEquals("string", \common\pdo_bool("string"));
		$this->assertEquals(0, \common\pdo_bool(0));
		$this->assertEquals(0, \common\pdo_bool(false));
		$this->assertEquals(1, \common\pdo_bool(true));
	}

	public function testShortClass() {
		$this->assertEquals("PersistentEnum", \common\short_class(\CsrDelft\Orm\Entity\PersistentEnum::class));
	}
}
