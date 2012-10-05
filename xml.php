<?php 

namespace oaiprovider\xml;

use oaiprovider\Header;

use oaiprovider AS oai;
use oaiprovider\OAIException;
use oaiprovider\output;

class NS {
  const DC = "http://purl.org/dc/elements/1.1/";
  const OAI_DC = "http://www.openarchives.org/OAI/2.0/oai_dc/";
  const ESE = "http://www.europeana.eu/schemas/ese/";

  const XML = "http://www.w3.org/2000/xmlns/";
  const XSI = "http://www.w3.org/2001/XMLSchema-instance";
}

function appendHeader($xmlnode, Header $header) {
  
  $headernode = appendElement($xmlnode, '', 'header');

  if ($header->deleted) {
    $headernode->setAttribute("status", "deleted");
  }

  appendElement($headernode, '', 'identifier', $header->identifier);
  appendElement($headernode, '', 'datestamp', output\datetime($header->datestamp));

  foreach($header->setSpec as $spec) {
    if (!empty($spec)) {
      appendElement($headernode, '', 'setSpec', $spec);
    }
  }
}

function appendRecord($node, Header $header, $metadata_xml) {
  $r = appendElement($node, '', 'record');
  appendHeader($r, $header);
  if ($metadata_xml) {
    $md = appendElement($r, '', 'metadata');
    $metadata = new \DOMDocument();
    $metadata->loadXML($metadata_xml);
    $imported = $node->ownerDocument->importNode($metadata->firstChild, true);
    $md->appendChild($imported);
  }
}

function appendElement($node, $ns, $tagName, $value=null) {
  if (empty($ns)) {
    $el = $node->ownerDocument->createElement($tagName);
  } else {
    $el = $node->ownerDocument->createElementNS($ns, $tagName);
  }
  if ($value !== null) {
    $el->nodeValue = output\escape($value);
  }
  $node->appendChild($el);
  return $el;
}

