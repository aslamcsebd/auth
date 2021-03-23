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
