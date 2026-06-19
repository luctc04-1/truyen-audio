<?php

namespace App\Shared\Constants;

class ApiConstants
{
    // Response messages
    const MSG_SUCCESS = 'Operation successful';
    const MSG_ERROR = 'Operation failed';
    const MSG_NOT_FOUND = 'Resource not found';
    const MSG_UNAUTHORIZED = 'Unauthorized';
    const MSG_FORBIDDEN = 'Forbidden';
    const MSG_VALIDATION_ERROR = 'Validation error';

    // HTTP Status Codes
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_INTERNAL_ERROR = 500;

    // Pagination
    const DEFAULT_PAGE = 1;
    const DEFAULT_PER_PAGE = 15;
    const MAX_PER_PAGE = 100;
}
