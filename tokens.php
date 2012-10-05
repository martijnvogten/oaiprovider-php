<?php 
namespace oaiprovider\tokens;

interface TokenStore {
  function storeToken($token, $data, $expirationDate);
  function fetchToken($token);
}

class DatabaseTokenStore implements TokenStore {
  function assertTable() {
    DB::query("
      CREATE TABLE IF NOT EXISTS oai_resumptiontoken (
        `token` varchar(255) NOT NULL,
        `data` longtext NOT NULL,
        `expirationDate` datetime NOT NULL,
        PRIMARY KEY (`token`)
      )
     ");
  }
  
  function storeToken($token, $data, $expirationDate) {
    $this->assertTable();
    
    DB::query("
        INSERT INTO oai_resumptiontoken 
          (token, data, expirationDate) 
        VALUES
          (
            " . DB::quote($token) . ",
            " . DB::quote($data) . ",
            " . DB::quote(date('Y-m-d H:i:s', $expirationDate)) . "
          )
    ");
  }
  
  function fetchToken($token) {
    $this->assertTable();
    
    $result = DB::query("SELECT data FROM oai_resumptiontoken WHERE token=" . DB::quote($token));
    $tokens = array();
    foreach($result as $row) {
      $tokens[] = $row['data'];
    }
    if (count($tokens) == 1) {
      return $tokens[0];
    }
    return null;
  }
}

class DB {
  const DSN = 'mysql:host=localhost;dbname=oai_tokens';
  const USER = 'root';
  const PASS = '';

  public static function getConnection() {
    static $conn;
    if ($conn == null) {
      $conn = new \PDO(self::DSN, self::USER, self::PASS);
    }
    return $conn;
  }

  public static function fetchRow($sql) {
    foreach(self::query($sql) as $row) {
      $rows[] = $row;
    }
    return $rows[0];
  }

  public static function query($sql) {
    return self::getConnection()->query($sql);
  }

  public static function quote($val) {
    return self::getConnection()->quote($val);
  }
}
