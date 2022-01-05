<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                $role = Auth::user()->role_id;

                if(!(Auth::user()->emp_status_id == 2)){
                switch ($role) {
                        case 1:
                            return redirect(RouteServiceProvider::ADMIN);
                            break;
                        case 2:
                            return redirect(RouteServiceProvider::EMPLOYEE);
                            break;
                        case 3:
                            return redirect(RouteServiceProvider::APPROVER);
                            break;

                        default:
                            return redirect('/')->with('error', 'Authentication failed. Please try again or contact HR.');
                            break;
                        }
                }
            }
        }

        return $next($request);
    }
}





