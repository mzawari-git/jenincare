<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'jenincare/public/api/track/*',
        'jenincare/public/admin/skinanalyzer/*',
        'admin/skinanalyzer/*',
    ];
}
