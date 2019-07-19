<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Exception;

use Exception;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class NotImplementedException extends Exception
{
    public function __construct(Throwable $previous = null)
    {
        $message = 'Producing response from exception is not implemented yet';

        parent::__construct($message, 0, $previous);
    }
}
