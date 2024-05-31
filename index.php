<?php

require 'vendor/autoload.php';

use App\Router;
use App\Controllers\Event;
use App\Controllers\Event_solo;
use App\Controllers\Login;
use App\Controllers\Register;
use PgSql\Lob;

new Router([
  'event' => Event::class,
  'event_solo/:id'=> Event_solo::class,
  'login' => Login::class,
  'register' => Register::class

  
]);