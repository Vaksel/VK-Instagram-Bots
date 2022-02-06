<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/registerFromMainSite',
        '/checkRegistration',
        '/login',
        '/i_api/register',
        '/i_api/add-comment',
        '/i_api/edit-comment',
        '/i_api/edit-action-comment',
        '/i_api/delete-comment',
        '/i_api/toggle-comment-treatment',
    ];
}
