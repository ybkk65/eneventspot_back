<?php

require 'vendor/autoload.php';

use App\Router;
use App\Controllers\Event;
use App\Controllers\Event_solo;
use App\Controllers\Login;
use App\Controllers\Register;
use App\Controllers\Authentification;
use App\Controllers\Logout;
use  App\Controllers\Users;
use App\Controllers\My_Event;
use App\Controllers\Recent_event;
use App\Controllers\Inscription;
use App\Controllers\MyInscription;
use App\Controllers\Inscription_status;
use App\Controllers\UpdateStatu;


new Router([
  'event' => Event::class,
  'event_solo/:id'=> Event_solo::class,
  'login' => Login::class,
  'register' => Register::class,
  'authentification'=> Authentification::class,
  'logout'=> Logout::class,
  'users'=> Users::class,
  'My_Event/:id'=> My_Event::class,
  'Recent_event/:id'=> Recent_event::class,
  'inscription'=> Inscription::class,
  'MyInscription/:id'=> MyInscription::class,
  'Inscription_status/:id'=> Inscription_status::class,
  'UpdateStatu' => UpdateStatu::class,
]);