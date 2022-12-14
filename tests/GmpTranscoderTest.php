<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Semperton\Multibase\Exception\DublicateCharsException;
use Semperton\Multibase\Exception\InvalidCharsException;
use Semperton\Multibase\Transcoder\GmpTranscoder;

final class GmpTranscoderTest extends TestCase
{
	protected function setUp(): void
	{
		if (!function_exists('gmp_init')) {
			$this->markTestSkipped('GMP extension is not installed');
		}
	}

	public function testHex(): void
	{
		$transcoder = new GmpTranscoder('0123456789abcdef');
		$data = 'Hello World';

		$encoded = $transcoder->encode($data);

		$this->assertEquals(bin2hex($data), $encoded);

		$decoded = $transcoder->decode($encoded);
		$this->assertEquals($data, $decoded);

		$data = chr(0) . chr(0) . chr(0) . chr(0) . chr(255) . chr(255) . chr(255) . chr(255);

		$encoded = $transcoder->encode($data);

		// need to pad zeros for hex
		$encoded = str_pad($encoded, strlen($data) * 2, '0', STR_PAD_LEFT);

		$this->assertEquals(bin2hex($data), $encoded);
	}

	public function testMultibyte(): void
	{
		$transcoder = new GmpTranscoder(
			'π§³πβοΈπ§΅πͺ‘πͺ’π§ΆππΆπ₯½π₯Όπ¦Ίππππ§£π§€π§₯π§¦πππ₯»π©΄π©±π©²' .
				'π©³πππππππππ₯Ύπ₯Ώπ π‘π©°π’πππ©ππ§’βπͺπππΌ'
		);
		$data = 'Hello World';

		$encoded = $transcoder->encode($data);

		$this->assertEquals('βπͺ’ππ©΄π©°π₯»πππ§’π©²π§₯π₯½π©πππ', $encoded);

		$decoded = $transcoder->decode($encoded);
		$this->assertEquals($data, $decoded);
	}

	public function testInvalidDecodeChars(): void
	{
		try {
			$transcoder = new GmpTranscoder('0123456789abcdef');
			$transcoder->decode('π₯½1Acf==βͺ');
		} catch (InvalidCharsException $ex) {
			$this->assertSame(['π₯½', 'A', '=', 'βͺ'], $ex->getChars());
		}
	}

	public function testDublicateAlphabetChars(): void
	{
		try {
			$transcoder = new GmpTranscoder('aBCadeffa');
		} catch (DublicateCharsException $ex) {
			$this->assertSame(['a', 'f'], $ex->getChars());
		}
	}

	public function testEmptyDecodeString(): void
	{
		$transcoder = new GmpTranscoder('0123456789');
		$encoded = $transcoder->decode('');

		$this->assertEquals('', $encoded);
	}

	public function testEmptyEncodeString(): void
	{
		$transcoder = new GmpTranscoder('0123456789');
		$encoded = $transcoder->encode('');

		$this->assertEquals('', $encoded);
	}
}
