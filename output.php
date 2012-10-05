<?php

namespace oaiprovider\output;

use oaiprovider as oai;
use oaiprovider\xml as xml;

function escape($str) {
  return htmlspecialchars($str);
}

function date($tstamp) {
  return gmdate('Y-m-d', $tstamp);
}

function datetime($tstamp=null) {
  if ($tstamp) {
    return gmdate("Y-m-d\TH:i:s\Z", $tstamp);
  } else {
    return gmdate("Y-m-d\TH:i:s\Z", 0);
  }
}

function _get_url() {
  preg_match('@^(https?).*@', strtolower($_SERVER['SERVER_PROTOCOL']), $matches);
  $prot = $matches[1];
  return $prot . "://" . $_SERVER['HTTP_HOST'] . _stripquery($_SERVER['REQUEST_URI']);
}

function _stripquery($uri) {
  return substr($uri, 0, strpos($uri, "?"));
}

function getBaseDocument($params) {
  $doc = new \DOMDocument('1.0', 'utf-8');
  $doc->formatOutput = true;

  $root = $doc->createElementNS("http://www.openarchives.org/OAI/2.0/", "OAI-PMH");
  $root->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
  $root->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance", "xsi:schemaLocation", "http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd");
  $doc->appendChild($root);

  xml\appendElement($root, "", "responseDate", datetime(time()));

  $request = xml\appendElement($root, "", "request", _get_url());

  $p = oai\parseParams($params, array(oai\P_VERB, oai\P_FROM, oai\P_UNTIL, oai\P_SET, oai\P_IDENTIFIER, oai\P_METADATAPREFIX, oai\P_RESUMPTIONTOKEN));
  foreach($p as $param => $val) {
    $request->setAttribute($param, escape($val));
  }
  return $root;
}

function send_error($params, $code, $message) {
  $root = getBaseDocument($params);
  $error = xml\appendElement($root, "", "error", $message);
  $error->setAttribute("code", escape($code));
  header("Content-Type: text/xml; charset=utf-8");
  send($root);
}

function send($doc) {
  header("Content-Type: text/xml; charset=utf-8");
  echo $doc->ownerDocument->saveXML();
}

