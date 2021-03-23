<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;
use Exception;
use Auth; 

class Handler extends ExceptionHandler{
   protected $dontReport = [ // ];

   protected $dontFlash = [ 'password', 'password_confirmation', ];

   public function report(Throwable $exception){
     parent::report($exception);
   }

   public function render($request, Throwable $exception){
     return parent::render($request, $exception);
   }

   //Main Exception code here
   protected function unauthenticated($request, AuthenticationException $exception){
      if ($request->expectsJson()) {
          return response()->json(['error' => 'Unauthenticated.'], 401);
      }
      if ($request->is('admin') || $request->is('admin/*')) {
          return redirect()->guest('/login/admin');
      }
      if ($request->is('writer') || $request->is('writer/*')) {
          return redirect()->guest('/login/writer');
      }
      return redirect()->guest(route('login'));
   }

}
