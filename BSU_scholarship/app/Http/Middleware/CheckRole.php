<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        if (session('role') !== $role) {
            // Redirect to appropriate dashboard based on actual role
            return redirect(match (session('role')) {
                'student' => '/student',
                'sfao'    => '/sfao',
                'central' => '/central',
                default   => '/login'
            })->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
