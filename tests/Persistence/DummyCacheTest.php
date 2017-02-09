<?php
use CsrDelft\Orm\Persistence\DummyCache;
use PHPUnit\Framework\TestCase;

/**
 * @covers CsrDelft\Orm\Persistence\DummyCache
 */
final class DummyCacheTest extends TestCase {
	public function testTrue() {
		$cache = new DummyCache();
		$this->assertTrue($cache->add());
		$this->assertTrue($cache->add("testing"));
	}

	/**
	 * The DummyCache can get calls to any function, these should all return false.
	 */
	public function testFalse() {
		/** @var object $cache */
		$cache = new DummyCache();
		$this->assertFalse($cache->testing());
		$this->assertFalse($cache->__call('test', ''));
	}
}
