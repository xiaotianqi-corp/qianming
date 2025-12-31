<?php

namespace App\Exceptions;

use Exception;

class ProductNotAvailableInCountryException extends Exception
{
    protected $message = 'The selected product is not available in the indicated country.';
    protected $code = 422;
}
