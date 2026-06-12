<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DisconnectAdminMl
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } finally {
            DB::disconnect('admin_ml');
        }
    }
}