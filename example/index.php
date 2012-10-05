<?php

namespace example;


use oaiprovider\xml\NS;

require_once '../endpoint.php';
require_once '../xml/europeana.php';
require_once '../xml/dublincore.php';

use oaiprovider\xml\EuropeanaRecord;
use oaiprovider\xml\DublinCoreRecord;
use oaiprovider\Repository;
use oaiprovider\Header;

date_default_timezone_set('Europe/Amsterdam');

class ExampleRepository implements Repository {
  
  public function getIdentifyData() {
    return array(
        'repositoryName' => 'Example OAI Provider',
        'baseUrl' => 'http://example.com/oai/',
        'protocolVersion' => '2.0',
        'adminEmail' => 'admin@example.com',
        'earliestDatestamp' => '1970-01-01T00:00:00Z',
        'deletedRecord' => 'persistent',
        'granularity' => 'YYYY-MM-DDThh:mm:ssZ');
  }
  
  public function getMetadataFormats() {
    return array(
        array(
            'metadataPrefix' => 'oai_dc',
            'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
            'metadataNamespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/',
        ),
        array(
            'metadataPrefix' => 'ese',
            'schema' => 'http://www.europeana.eu/schemas/ese/ESE-V3.3.xsd',
            'metadataNamespace' => 'http://www.europeana.eu/schemas/ese/',
        ),
    );
  }

  public function getSets() {
    return array(
        array('spec' => 'FICTION' , 'name' => 'All books in the fiction category'),
        array('spec' => 'NON_FICTION' , 'name'  => 'All books in the non-fiction category')
    );
  }

  public function getIdentifiers($from, $until, $set, $last_identifier, $max_results) {
    $sql = "SELECT id FROM books";

    $where = array();
    if ($from) {
      $where[] = "lastchanged >= " . DB::quote(date('Y-m-d H:i:s', $from));
    }
    if ($until) {
      $where[] = "lastchanged <= " . DB::quote(date('Y-m-d H:i:s', $until));
    }
    if ($set) {
      $where[] = "category = " . DB::quote($set);
    }
    if ($last_identifier) {
      $where[] = "id > " . DB::quote($last_identifier);
    }

    if (count($where)) {
      $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY id ASC";

    if ($max_results) {
      $sql .= " LIMIT " . $max_results;
    }
    
    $ids = array();
    foreach(DB::query($sql) as $row) {
      $ids[] = $row['id'];
    }
    return $ids;
  }

  public function getHeader($identifier) {
    $row = DB::fetchRow("SELECT id, lastchanged, category, deleted FROM books WHERE id=" . DB::quote($identifier));
    
    $header = new Header();
    $header->identifier = $row['id'];
    $header->datestamp = strtotime($row['lastchanged']);
    if ($row['category']) {
      $header->setSpec = array($row['category']);
    }
    $header->deleted = ($row['deleted'] == 1);
    return $header;
  }

  public function getMetadata($metadataPrefix, $identifier) {
    $row = DB::fetchRow("SELECT * FROM books WHERE id=" . DB::quote($identifier));
    if ($row['deleted'] == 1) {
      return null;
    }

    switch($metadataPrefix) {
      case 'oai_dc':
        $dcrec = new DublinCoreRecord();
        $dcrec->addNS(NS::DC, 'title', $row['title']);
        $dcrec->addNS(NS::DC, 'description', $row['description']);
        return $dcrec->toXml();
      case 'ese':
        $eserec = new EuropeanaRecord();
        $eserec->addNS(NS::DC, 'title', $row['title']);
        $eserec->addNS(NS::DC, 'description', $row['description']);
        return $eserec->toXml();
    }
  }
  
}

class DB {
  const DSN = 'mysql:host=localhost;dbname=library';
  const USER = 'root';
  const PASS = '';
  
  static function getConnection() {
    static $conn;
    if ($conn == null) {
      $conn = new \PDO(self::DSN, self::USER, self::PASS);
    }
    return $conn;
  }
  
  static function fetchRow($sql) {
    foreach(self::query($sql) as $row) {
      $rows[] = $row;
    }
    return $rows[0];
  }
  
  static function query($sql) {
    $result = self::getConnection()->query($sql);
    if (!$result) {
      throw new Exception(self::getConnection()->errorInfo());
    }
    return $result;
  }
  
  static function quote($val) {
    return self::getConnection()->quote($val);
  }
}


\oaiprovider\handleRequest($_GET, new ExampleRepository);
