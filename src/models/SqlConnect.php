<?php

namespace App\Models;

use \PDO;

class SqlConnect {
  public object $db;
  private string $socket;
  private string $dbname;
  private string $password;
  private string $user;

  public function __construct() {
    $this->socket = '/Applications/MAMP/tmp/mysql/mysql.sock'; 
    $this->dbname = 'event_app';
    $this->user = 'youness';
    $this->password = '0000';

    $this->db = new PDO(
      'mysql:unix_socket='.$this->socket.';dbname='.$this->dbname,
      $this->user,
      $this->password
    );

    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->db->setAttribute(PDO::ATTR_PERSISTENT, false);
  }

  public function transformDataInDot($data) {
    $dataFormated = [];

    foreach ($data as $key => $value) {
      $dataFormated[':' . $key] = $value;
    }

    return $dataFormated;
  }
}