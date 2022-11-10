<?php

declare(strict_types=1);

namespace Semperton\Multibase;

use Semperton\Multibase\Transcoder\BaseTranscoder;

final class Base58 extends BaseTranscoder
{
	public function __construct()
	{
		parent::__construct('123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz');
	}
}
