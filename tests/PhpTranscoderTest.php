<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Semperton\Multibase\Exception\DublicateCharsException;
use Semperton\Multibase\Exception\InvalidCharsException;
use Semperton\Multibase\Transcoder\PhpTranscoder;

final class PhpTranscoderTest extends TestCase
{
	public function testHex(): void
	{
		$transcoder = new PhpTranscoder('0123456789abcdef');
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
		$transcoder = new PhpTranscoder(
			'🧳🌂☂️🧵🪡🪢🧶👓🕶🥽🥼🦺👔👕👖🧣🧤🧥🧦👗👘🥻🩴🩱🩲' .
				'🩳👙👚👛👜👝🎒👞👟🥾🥿👠👡🩰👢👑👒🎩🎓🧢⛑🪖💄💍💼'
		);
		$data = 'Hello World';

		$encoded = $transcoder->encode($data);

		$this->assertEquals('☂🪢👟🩴🩰🥻👚👙🧢🩲🧥🥽🎩👙👝🎒', $encoded);

		$decoded = $transcoder->decode($encoded);
		$this->assertEquals($data, $decoded);
	}

	public function testInvalidDecodeChars(): void
	{
		try {
			$transcoder = new PhpTranscoder('0123456789abcdef');
			$transcoder->decode('1Acf=');
		} catch (InvalidCharsException $ex) {
			$this->assertSame([1 => 'A', 4 => '='], $ex->getChars());
		}
	}

	public function testDublicateAlphabetChars(): void
	{
		try {
			$transcoder = new PhpTranscoder('aBCadeff');
		} catch (DublicateCharsException $ex) {
			$this->assertSame([3 => 'a', 7 => 'f'], $ex->getChars());
		}
	}

	public function testEmptyDecodeString(): void
	{
		$transcoder = new PhpTranscoder('0123456789');
		$encoded = $transcoder->decode('');

		$this->assertEquals('', $encoded);
	}

	public function testEmptyEncodeString(): void
	{
		$transcoder = new PhpTranscoder('0123456789');
		$encoded = $transcoder->encode('');

		$this->assertEquals('', $encoded);
	}
}