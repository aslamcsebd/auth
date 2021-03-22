<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Student extends Model{

   protected $guard = 'student';
   protected $fillable = [ 'name', 'email', 'password'];
   protected $hidden = [ 'password'];
}
