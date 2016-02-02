<?php

class IpFilterTest extends \Codeception\TestCase\Test
{
	public function testIPv4CIDR()
	{
		$this->assertTrue(Rhymix\Framework\IpFilter::inRange('10.0.127.191', '10.0.127.191'));
		$this->assertFalse(Rhymix\Framework\IpFilter::inRange('10.1.131.177', '10.1.131.178'));
		$this->assertTrue(Rhymix\Framework\IpFilter::inRange('127.0.0.1', '127.0.0.0/8'));
		$this->assertFalse(Rhymix\Framework\IpFilter::inRange('172.34.0.0', '172.16.0.0/12'));
		$this->assertTrue(Rhymix\Framework\IpFilter::inRange('192.168.18.214', '192.168.16.0/22'));
		$this->assertFalse(Rhymix\Framework\IpFilter::inRange('192.168.18.214', '192.168.16.0/23'));
	}
	
	public function testIPv6CIDR()
	{
		$this->assertTrue(Rhymix\Framework\IpFilter::inRange('::1', '::1/128'));
		$this->assertFalse(Rhymix\Framework\IpFilter::inRange('::1', '::2'));
		$this->assertTrue(Rhymix\Framework\IpFilter::inRange('2400:cb00::1234', '2400:cb00::/32'));
		$this->assertFalse(Rhymix\Framework\IpFilter::inRange('2405:8100::1234', '2400:cb00::/32'));
	}
	
	public function testIPv4Wildcard()
	{
		$this->assertTrue(Rhymix\Framework\IpFilter::inRange('192.168.134.241', '192.168.134.*'));
		$this->assertTrue(Rhymix\Framework\IpFilter::inRange('192.168.134.241', '192.168.*.*'));
		$this->assertFalse(Rhymix\Framework\IpFilter::inRange('192.168.134.241', '192.168.136.*'));
		$this->assertFalse(Rhymix\Framework\IpFilter::inRange('192.168.134.241', '192.172.*.*'));
	}
	
	public function testIPv4Hyphen()
	{
		$this->assertTrue(Rhymix\Framework\IpFilter::inRange('192.168.134.241', '192.168.134.0-192.168.134.255'));
		$this->assertTrue(Rhymix\Framework\IpFilter::inRange('192.168.134.241', '192.168.128.16-192.168.145.0'));
		$this->assertFalse(Rhymix\Framework\IpFilter::inRange('192.168.134.241', '192.168.134.242-192.168.244.7'));
		$this->assertFalse(Rhymix\Framework\IpFilter::inRange('192.168.134.241', '192.168.100.255-192.168.133.19'));
	}
	
	public function testValidator()
	{
		$this->assertTrue(Rhymix\Framework\IpFilter::validateRange('192.168.0.1'));
		$this->assertTrue(Rhymix\Framework\IpFilter::validateRange('192.168.0.0/16'));
		$this->assertTrue(Rhymix\Framework\IpFilter::validateRange('192.168.*.*'));
		$this->assertTrue(Rhymix\Framework\IpFilter::validateRange('192.168.*'));
		$this->assertTrue(Rhymix\Framework\IpFilter::validateRange('192.168.0.0-192.168.255.255'));
		$this->assertTrue(Rhymix\Framework\IpFilter::validateRange('2400:cb00::/32'));
		$this->assertFalse(Rhymix\Framework\IpFilter::validateRange('192.168.0.0~192.168.255.255'));
	}
	
	public function testLegacy()
	{
		$this->assertTrue(\IpFilter::filter(array('192.168.134.241'), '192.168.134.241'));
		$this->assertTrue(\IpFilter::filter(array('192.168.134.0-192.168.134.255'), '192.168.134.241'));
		$this->assertTrue(\IpFilter::filter(array('127.0.0.1', '192.168.134.241'), '192.168.134.241'));
		$this->assertTrue(\IpFilter::filter(array('192.168.134.*'), '192.168.134.241'));
		$this->assertTrue(\IpFilter::filter(array('192.168.*'), '192.168.134.241'));
		$this->assertFalse(\IpFilter::filter(array('127.0.0.1'), '192.168.134.241'));
	}
	
	public function testCloudFlareRealIP()
	{
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '192.168.134.241';
		
		$_SERVER['REMOTE_ADDR'] = '192.168.10.1';
		$this->assertFalse(Rhymix\Framework\IpFilter::getCloudFlareRealIP());
		$this->assertEquals('192.168.10.1', $_SERVER['REMOTE_ADDR']);
		
		$_SERVER['REMOTE_ADDR'] = '108.162.192.121';
		$this->assertEquals('192.168.134.241', Rhymix\Framework\IpFilter::getCloudFlareRealIP());
		$this->assertEquals('192.168.134.241', $_SERVER['REMOTE_ADDR']);
		
		unset($_SERVER['HTTP_CF_CONNECTING_IP']);
		$_SERVER['REMOTE_ADDR'] = '192.168.10.1';
		$this->assertFalse(Rhymix\Framework\IpFilter::getCloudFlareRealIP());
		$this->assertEquals('192.168.10.1', $_SERVER['REMOTE_ADDR']);
	}
}