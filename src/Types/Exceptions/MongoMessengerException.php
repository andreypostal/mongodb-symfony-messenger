<?php
namespace Andrey\Symfony\Messenger\Mongo\Types\Exceptions;

use Exception;
use Throwable;

class MongoMessengerException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
