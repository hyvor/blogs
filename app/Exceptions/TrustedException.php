<?php

namespace App\Exceptions;

/**
 * This class is extended by Exception classes that
 * throw trusted errors (doesn't contain anything like class name or table names)
 *
 * All Exception classes inside the application (which we write)
 * extends this.
 *
 * This is used in App\Exceptions\Handler to return error responses
 * in API endpoints.
 *
 * Exceptions that does not extend TrustedException are not shown to the user.
 * They can contain sensitive information.
 */

class TrustedException extends \Exception
{
    const ERROR_BAD_REQUEST = 400; // This means that client-side input fails validation.
    const ERROR_UNAUTHORIZED = 401; // This means the user isn’t authenticated.
    const ERROR_FORBIDDEN = 403; // This means the user is authenticated, but it’s not allowed to access a resource.
    const ERROR_NOT_FOUND = 404; // not found
}
