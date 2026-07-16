<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Membatasi halaman hanya untuk pengguna admin.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->role !== 'admin') {
            abort(
                403,
                'Halaman ini hanya dapat diakses oleh admin.'
            );
        }

        return $next($request);
    }
}