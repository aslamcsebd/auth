<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated{
   public function handle($request, Closure $next, $guard = null){
      
      if (Auth::guard($guard)->check()) {
         return redirect('/home');
         /* Same meaning bellow
            use App\Providers\RouteServiceProvider;
            return redirect(RouteServiceProvider::HOME);
         */
      }

      if ($guard == "admin" && Auth::guard($guard)->check()) {
          return redirect('/admin');
      }

      if ($guard == "writer" && Auth::guard($guard)->check()) {
          return redirect('/writer');
      }
      return $next($request);
   }
}
