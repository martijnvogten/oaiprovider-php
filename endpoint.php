<?php

namespace oaiprovider;

use oaiprovider\tokens\DatabaseTokenStore;

require_once 'constants.php';
require_once 'output.php';
require_once 'xml.php';
require_once 'parameters.php';
require_once 'repository.php';
require_once 'tokens.php';

const MAX_RESULTS = 100;

function handleRequest($params, Repository $repository, TokenStore $tokenstore=null) {
  
  if (!$tokenstore) {
    $tokenstore = new DatabaseTokenStore();
  }
  
  // check arguments
  $verb = @$params[P_VERB];
  
  if (empty($verb)) {
    output\send_error(array(), ERR_BAD_ARGUMENT, "request did not specify a verb");
    return;
  }

  try {
    
    $doc = output\getBaseDocument($params);

    switch($verb) {
      case "GetRecord":
        $p = assertParams($params, array(P_VERB, P_IDENTIFIER, P_METADATAPREFIX));
        validateFormat($repository->getMetadataFormats(), $p[P_METADATAPREFIX]);
        
        $header = $repository->getHeader($p[P_IDENTIFIER]);
        if ($header == null) {
            throw new OAIException(ERR_ID_DOES_NOT_EXIST, '');
        }
        
        $metadata = $repository->getMetadata($p[P_METADATAPREFIX], $header->identifier);
        
        $gr = xml\appendElement($doc, '', 'GetRecord');
        xml\appendRecord($gr, $header, $metadata);
        break;

      case "ListIdentifiers":
      case "ListRecords":
        $last_id = null;
        $was_resumption = false;
        if (array_key_exists(P_RESUMPTIONTOKEN, $params)) {
          $p = assertParams($params, array(P_VERB, P_RESUMPTIONTOKEN));
          $data = $tokenstore->fetchToken($p[P_RESUMPTIONTOKEN]);
          if ($data == null) {
            throw new OAIException(ERR_BAD_RESUMPTION_TOKEN, '');
          }
          $token = unserialize($data);
          $p = $token->params;
          $last_id = $token->last_id;
          $was_resumption = true;
        } else {
          $p = assertParams($params, array(P_VERB, P_METADATAPREFIX), array(P_FROM, P_UNTIL, P_SET));
        }
        
        validateFormat($repository->getMetadataFormats(), $p[P_METADATAPREFIX]);
        validateSet($repository->getSets(), @$p[P_SET]);
        
        $from = @$p[P_FROM] ? strtotime(@$p[P_FROM]) : null;
        $until = @$p[P_UNTIL] ? strtotime(@$p[P_UNTIL]) : null;
        $ids = $repository->getIdentifiers($from, $until, @$p[P_SET], $last_id, MAX_RESULTS + 1);
        
        $hasmore = false;
        if (count($ids) > MAX_RESULTS) {
          $hasmore = true;
          
          array_pop($ids);
          $last_id = end($ids);
        }
        
        $li = xml\appendElement($doc, '', $verb);
        
        foreach($ids as $id) {
          $header = $repository->getHeader($id);
          if ($verb == 'ListIdentifiers') {
            xml\appendHeader($li, $header);
          } else {
            xml\appendRecord($li, $header, $repository->getMetadata($p[P_METADATAPREFIX], $id));
          }
        }
        
        if ($hasmore) {
          $token = _createResumptionToken($p, $last_id);
          $tokenstore->storeToken($token->token, serialize($token), $token->expirationDate);
          
          $rt = xml\appendElement($li, '', 'resumptionToken', $token->token);
          $rt->setAttribute('expirationDate', output\datetime($token->expirationDate));
        } else if ($was_resumption) {
          // Last set of incomplete lists should contain an empty resumptionToken.
          xml\appendElement($li, '', 'resumptionToken');
        }
        break;

      case "ListSets":
        assertParams($params, array(P_VERB));
        _listSets($doc, $repository->getSets());
        break;

      case "ListMetadataFormats":
        $p = assertParams($params, array(P_VERB), array(P_IDENTIFIER));
        
        if (@$p[P_IDENTIFIER]) {
	        $header = $repository->getHeader($p[P_IDENTIFIER]);
	        if ($header == null) {
	        	throw new OAIException(ERR_ID_DOES_NOT_EXIST, '');
	        }
        }
        
        _listMetadataFormats($doc, $repository->getMetadataFormats(@$p[P_IDENTIFIER]));
        break;

      case "Identify":
        assertParams($params, array(P_VERB));
        _identify($doc, $repository->getIdentifyData());
        break;

      default:
        throw new OAIException(ERR_BAD_VERB, "verb is not valid");
    }
    output\send($doc);

  } catch (OAIException $e) {
    output\send_error($params, $e->getOAICode(), $e->getMessage());
  }
}

function _listSets($node, $sets) {
  $ls = xml\appendElement($node, '', 'ListSets');

  foreach($sets as $set) {
    $s = xml\appendElement($ls, '', 'set');
    xml\appendElement($s, '', 'setSpec', $set['spec']);
    xml\appendElement($s, '', 'setName', $set['name']);
  }
}

function _identify($node, $identify_data) {
  $identify = xml\appendElement($node, '', 'Identify');

  foreach($identify_data as $name => $value) {
    xml\appendElement($identify, '', $name, $value);
  }
}

function _listMetadataFormats($node, $formats) {
  $lmf = xml\appendElement($node, '', 'ListMetadataFormats');
  foreach($formats as $format) {
    $mf = xml\appendElement($lmf, '', 'metadataFormat');
    xml\appendElement($mf, '', 'metadataPrefix', $format['metadataPrefix']);
    xml\appendElement($mf, '', 'schema', $format['schema']);
    xml\appendElement($mf, '', 'metadataNamespace', $format['metadataNamespace']);
  }
}

function _createResumptionToken($params, $last_id) {
  $rt = new ResumptionToken();
  $rt->last_id = $last_id;
  $rt->params = $params;
  $rt->token = uniqid("rt");
  $rt->expirationDate = strtotime("+1 days");
  return $rt;
}

class OAIException extends \Exception {
  private $oai_code;
  
  public function __construct($oai_code, $message) {
    parent::__construct($message);
    $this->oai_code = $oai_code;
  }
  
  public function getOAICode() {
    return $this->oai_code;
  }
}

class ResumptionToken {
  public $token;
  public $last_id;
  public $params;
  public $expirationDate;
}

