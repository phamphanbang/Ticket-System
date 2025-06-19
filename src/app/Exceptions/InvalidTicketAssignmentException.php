<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class InvalidTicketAssignmentException extends Exception
{
    public function __construct(
        string $message = "Invalid ticket assignment.",
        int $code = Response::HTTP_UNPROCESSABLE_ENTITY,
    ) {
        parent::__construct($message, $code);
    }


}
