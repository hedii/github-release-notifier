<?php

namespace App\Services\Github\Exceptions;

use Exception;
use Throwable;

class RepositoryNotFound extends Exception
{
    /**
     * RepositoryNotFound constructor.
     *
     * @param string $username
     * @param string $repository
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $username, string $repository, int $code = 0, Throwable $previous = null)
    {
        parent::__construct("The Github repository {$username}/{$repository} does not exist", $code, $previous);
    }
}
