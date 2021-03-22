## Laravel Multiple Authentication


-  Make model [Admin, Student]
-  Go to model and Add it 
   [  protected $guard = 'admin';
      protected $fillable = [ 'name', 'email', 'password'];
      protected $hidden = [ 'password'];
   ]
   [  protected $guard = 'student';
      protected $fillable = [ 'name', 'email', 'password'];
      protected $hidden = [ 'password'];
   ]

-  Go to [config/auth.php] add the code
   [guards]
      'admin' => [
         'driver' => 'session',
         'provider' => 'admins',
      ],
     'student' => [
         'driver' => 'session',
         'provider' => 'students',
      ],

-  [providers]
      'admins' => [
         'driver' => 'eloquent',
         'model' => App\Admin::class,
      ],
      'students' => [
         'driver' => 'eloquent',
         'model' => App\Student::class,
      ],
   [passwords] Not Mendatory