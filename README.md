### Laravel Multiple Authentication

1) Make Two(2) Model [Admin, Writer]
   <p>Migration</p>
   <details>
      <summary>Admin</summary>
      php artisan make:model Admin -m
      <pre>
         Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_super')->default(false);
            $table->rememberToken();
            $table->timestamps();
         });
      </pre>
   </details>

   <details>
      <summary>Writer</summary>
      <p>php artisan make:model Writer -m</p>
      <pre>
         Schema::create('writers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_editor')->default(false);
            $table->rememberToken();
            $table->timestamps();
         });
      </pre>
   </details>
   php artisan migrate

2) Model Edit
   <details>
      <summary>Admin</summary>
      <pre>
         // app/Admin.php
         <?php
            namespace App;
            use Illuminate\Notifications\Notifiable;
            use Illuminate\Foundation\Auth\User as Authenticatable;

            class Admin extends Authenticatable{
               use Notifiable;
               protected $guard = 'admin';
               protected $fillable = [ 'name', 'email', 'password', ];
               protected $hidden = [ 'password', 'remember_token', ];
            }
      </pre>
   </details>

   <details>
      <summary>Writer</summary>
      php artisan make:model Admin -m
      <pre>
         // app/Writer.php
         <?php
            namespace App;
            use Illuminate\Notifications\Notifiable;
            use Illuminate\Foundation\Auth\User as Authenticatable;

            class Writer extends Authenticatable{
               use Notifiable;
               protected $guard = 'writer';
               protected $fillable = [ 'name', 'email', 'password', ];
               protected $hidden = [ 'password', 'remember_token', ];
            }
      </pre>
   </details>

3) Define the guards

   <details>
      <summary>Guards</summary>
      <pre>
         // config/auth.php
         'guards' => [
            [...]
            'admin' => [
               'driver' => 'session',
               'provider' => 'admins',
            ],
            'writer' => [
               'driver' => 'session',
               'provider' => 'writers',
            ],
         ],
         [...]
      </pre>
   </details>

   <details>
      <summary>Providers</summary>
      <pre>
         // config/auth.php
         [...]
         'providers' => [
            [...]
            'admins' => [
               'driver' => 'eloquent',
               'model' => App\Admin::class,
            ],
            'writers' => [
               'driver' => 'eloquent',
               'model' => App\Writer::class,
            ],
         ],
         [...]         
      </pre>
   </details>

4) Set up the controllers

   <details>
      <summary>Modify RegisterController</summary>
      <pre>
         // app/Http/Controllers/Auth/RegisterController.php
         <?php

         namespace App\Http\Controllers\Auth;

         use App\Http\Controllers\Controller;
         use App\Providers\RouteServiceProvider;
         use App\Admin;
         use App\Writer;
         use Illuminate\Foundation\Auth\RegistersUsers;
         use Illuminate\Support\Facades\Hash;
         use Illuminate\Support\Facades\Validator;
         use Illuminate\Http\Request;

         class RegisterController extends Controller{
            use RegistersUsers;
            protected $redirectTo = RouteServiceProvider::HOME;
            
            public function __construct(){
               $this->middleware('guest');
               $this->middleware('guest:admin');
               $this->middleware('guest:writer');
            }

            //User
               protected function validator(array $data){
                  return Validator::make($data, [
                     'name' => ['required', 'string', 'max:255'],
                     'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                     'password' => ['required', 'string', 'min:8', 'confirmed'],
                  ]);
               }

               protected function create(array $data){
                  return User::create([
                     'name' => $data['name'],
                     'email' => $data['email'],
                     'password' => Hash::make($data['password']),
                  ]);
               }

            //Admin
               public function showAdminRegisterForm(){
                  return view('auth.register', ['url' => 'admin']);
               }

               protected function createAdmin(Request $request){
                  $this->validator($request->all())->validate();
                  $admin = Admin::create([
                     'name' => $request['name'],
                     'email' => $request['email'],
                     'password' => Hash::make($request['password']),
                  ]);
                  return redirect()->intended('login/admin');
               }

            //Writer
               public function showWriterRegisterForm(){
                  return view('auth.register', ['url' => 'writer']);
               }

               protected function createWriter(Request $request){
                  $this->validator($request->all())->validate();
                  $writer = Writer::create([
                     'name' => $request['name'],
                     'email' => $request['email'],
                     'password' => Hash::make($request['password']),
                  ]);
                  return redirect()->intended('login/writer');
               }
         }
      </pre>
   </details>

   <details>
      <summary>Modify LoginController</summary>
      <pre>
         // app/Http/Controllers/Auth/LoginController.php
         <?php
         namespace App\Http\Controllers\Auth;

         use App\Http\Controllers\Controller;
         use App\Providers\RouteServiceProvider;
         use Illuminate\Foundation\Auth\AuthenticatesUsers;
         use Illuminate\Http\Request;
         use Auth;

         class LoginController extends Controller {

            use AuthenticatesUsers;
            protected $redirectTo = RouteServiceProvider::HOME;

            public function __construct(){
               $this->middleware('guest')->except('logout');
               $this->middleware('guest:admin')->except('logout');
               $this->middleware('guest:writer')->except('logout');
            }

            //Admin
               public function showAdminLoginForm(){
                  return view('auth.login', ['url' => 'admin']);
               }

               public function adminLogin(Request $request){
                  $this->validate($request, [
                     'email'   => 'required|email',
                     'password' => 'required|min:6'
                  ]);

                  if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))){
                     return redirect()->intended('/admin');
                  }
                  return back()->withInput($request->only('email', 'remember'));
               }

            //Writer
               public function showWriterLoginForm(){
                 return view('auth.login', ['url' => 'writer']);
               }

               public function writerLogin(Request $request){
                  $this->validate($request, [
                     'email'   => 'required|email',
                     'password' => 'required|min:6'
                  ]);

                  if (Auth::guard('writer')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
                     return redirect()->intended('/writer');
                  }
                  return back()->withInput($request->only('email', 'remember'));
               }
         }
      </pre>
   </details>

5) Set up authentication pages

   <p>php artisan make:auth</p>
   <details>
      <summary>register.blade.php</summary>
      <pre>
         // resources/views/auth/register.blade.php
         [...]
         <div class="container">
            <div class="row justify-content-center">
               <div class="col-md-8">
                  <div class="card">
                     <div class="card-header"> {{ isset($url) ? ucwords($url) : ""}} {{ __('Register') }}</div>
                     <div class="card-body">
                        @isset($url)
                           <form method="POST" action='{{ url("register/$url") }}' aria-label="{{ __('Register') }}">
                        @else
                           <form method="POST" action="{{ route('register') }}" aria-label="{{ __('Register') }}">
                        @endisset
                           @csrf

            [...]
         </div>
      </pre>
   </details>

   <details>
      <summary>login.blade.php</summary>      
         // resources/views/auth/login.blade.php
         [...]
         <div class="container">
           <div class="row justify-content-center">
               <div class="col-md-8">
                   <div class="card">
                       <div class="card-header"> {{ isset($url) ? ucwords($url) : ""}} {{ __('Login') }}</div>
                       <div class="card-body">
                           @isset($url)
                           <form method="POST" action='{{ url("login/$url") }}' aria-label="{{ __('Login') }}">
                           @else
                           <form method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}">
                           @endisset
                               @csrf

           [...]
         </div>      
   </details>

6) Create the pages authenticated users will access
      
   <p> touch resources/views/layouts/app.blade.php </p>
       //N:B: This page already create...
   <p> touch resources/views/admin.blade.php </p>
   <p> touch resources/views/writer.blade.php </p>
   <p> touch resources/views/home.blade.php </p>
       //N:B: This page already create...

   <details>
      <summary>app.blade.php</summary>
      <pre>
         // resources/views/layouts/app.blade.php
         <!doctype html>
         <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
            <head>
               <meta charset="utf-8">
               <meta name="viewport" content="width=device-width, initial-scale=1">
               <!-- CSRF Token -->
               <meta name="csrf-token" content="{{ csrf_token() }}">
               <title>{{ config('app.name', 'Laravel') }}</title>
               <!-- Scripts -->
               <script src="{{ asset('js/app.js') }}" defer></script>
               <!-- Fonts -->
               <link rel="dns-prefetch" href="//fonts.gstatic.com">
               <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
               <!-- Styles -->
               <link href="{{ asset('css/app.css') }}" rel="stylesheet">
            </head>
            <body>
               <div id="app">
                  <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                     <div class="container">
                        <a class="navbar-brand" href="{{ url('/') }}">
                           {{ config('app.name', 'Laravel') }}
                        </a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                           <ul class="navbar-nav mr-auto">
                           </ul>
                           <ul class="navbar-nav ml-auto">
                              @guest
                                 <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                 </li>
                                 @if (Route::has('register'))
                                    <li class="nav-item">
                                       <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                    </li>
                                 @endif
                              @else
                                 <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                       {{-- {{ Auth::user()->name }} --}}
                                       Hi There
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                       <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }} </a>
                                       <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                          @csrf
                                       </form>
                                    </div>
                                 </li>
                              @endguest
                           </ul>
                        </div>
                     </div>
                  </nav>
                  <main class="py-4">
                     @yield('content')
                  </main>
               </div>
            </body>
         </html>
      </pre>
   </details>

   <details>
      <summary>home.blade.php</summary>
      <pre>
         // resources/views/home.blade.php
         @extends('layouts.app')
         @section('content')
         <div class="container">
            <div class="row justify-content-center">
               <div class="col-md-8">
                  <div class="card">
                     <div class="card-header">Dashboard</div>
                     <div class="card-body">
                        This is regular user!
                     </div>
                  </div>
               </div>
            </div>
         </div>
         @endsection
      </pre>
   </details>

   <details>
      <summary>admin.blade.php</summary>
      <pre>
         // resources/views/admin.blade.php
         @extends('layouts.app')
         @section('content')
         <div class="container">
            <div class="row justify-content-center">
               <div class="col-md-8">
                  <div class="card">
                     <div class="card-header">Dashboard</div>
                     <div class="card-body">
                        This is admin!
                     </div>
                  </div>
               </div>
            </div>
         </div>
         @endsection
      </pre>
   </details>
   
   <details>
      <summary>writer.blade.php</summary>
      <pre>
         // resources/views/writer.blade.php
         @extends('layouts.app')
         @section('content')
         <div class="container">
            <div class="row justify-content-center">
               <div class="col-md-8">
                  <div class="card">
                     <div class="card-header">Dashboard</div>
                     <div class="card-body">
                        This is writer!
                     </div>
                  </div>
               </div>
            </div>
         </div>
         @endsection
      </pre>
   </details> 

7) Set up the routes
   <details>
      <summary>routes/web.php</summary>
      <pre>
         <?php
         use Illuminate\Support\Facades\Route;
         Route::view('/', 'welcome');
         Auth::routes();

         Route::get('/login/admin', 'Auth\LoginController@showAdminLoginForm');
         Route::get('/login/writer', 'Auth\LoginController@showWriterLoginForm');
         Route::get('/register/admin', 'Auth\RegisterController@showAdminRegisterForm');
         Route::get('/register/writer', 'Auth\RegisterController@showWriterRegisterForm');

         Route::post('/login/admin', 'Auth\LoginController@adminLogin');
         Route::post('/login/writer', 'Auth\LoginController@writerLogin');
         Route::post('/register/admin', 'Auth\RegisterController@createAdmin');
         Route::post('/register/writer', 'Auth\RegisterController@createWriter');

         Route::view('/home', 'home')->middleware('auth');
         Route::view('/admin', 'admin');
         Route::view('/writer', 'writer');
      </pre>
   </details>

8) Modify how our users are redirected if authenticated
   <details>
      <summary>Redirected if authenticated</summary>
      <pre>
         // app/Http/Controllers/Middleware/RedirectIfAuthenticated.php
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
      </pre>
   </details>

9) Modify authentication exception handler
   <details>
      <summary></summary>
      <pre>
         // app/Exceptions/Handler.php
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
      </pre>
   </details>

   <details>
      <summary></summary>
      <pre>
      </pre>
   </details>

   <details>
      <summary></summary>
      <pre>
      </pre>
   </details>

