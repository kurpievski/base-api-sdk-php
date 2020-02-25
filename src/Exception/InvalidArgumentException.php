<?php

namespace BaseApi\Exception;

use BaseApi\Contracts\ExchangeExceptionInterface;

/**
 * Class InvalidArgumentException
 * @package BaseApi\Exception
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExchangeExceptionInterface
{
}
