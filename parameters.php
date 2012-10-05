<?php 

namespace oaiprovider;

function validateFormat($formats, $formatparam) {
  $prefixes = _extract($formats, "metadataPrefix");
  _validateOneOf($prefixes, $formatparam, ERR_CANNOT_DISSEMINATE_FORMAT, "");
}

function validateSet($sets, $setparam) {
  $specs = _extract($sets, "spec");
  _validateOneOf($specs, $setparam, ERR_BAD_ARGUMENT, "No such set");
}

function _validateOneOf($arr, $val, $err, $errmsg) {
  if (empty($val)) {
    return;
  }
  if (array_search($val, $arr) === FALSE) {
    throw new OAIException($err, $errmsg);
  }
}

function _extract($arr, $key) {
  $result = array();
  foreach($arr as $a) {
    $result[] = $a[$key];
  }
  return $result;
}

function parseParams($params, $which) {
  $result = array();

  foreach($which as $param) {
    switch($param) {
      case P_FROM:
      case P_UNTIL:
        $val = _parseDateParam(@$params[$param]);
        break;
      case P_SET:
      case P_VERB:
      case P_IDENTIFIER:
      case P_METADATAPREFIX:
      case P_RESUMPTIONTOKEN:
        $val = @$params[$param];
        break;
      default:
        throw new \Exception("Parsing for parameter $param not implemented");
    }
    if (!empty($val)) {
      $result[$param] = $val;
    }
  }
  return $result;
}

function assertParams($params, $required, $optional=array()) {
  $all = array_merge($required, $optional);
  $parsed = parseParams($params, $all);
  foreach($required as $param) {
    if (!array_key_exists($param, $parsed)) {
      throw new OAIException(ERR_BAD_ARGUMENT, "Missing parameter $param");
    }
  }
  foreach($params as $key => $v) {
    if (array_search($key, $all) === FALSE) {
      throw new OAIException(ERR_BAD_ARGUMENT, "Illegal parameter $key");
    }
  }
  return $parsed;
}


function _parseDateParam($str) {
  if (empty($str)) {
    return null;
  }
  // The legitimate formats are YYYY-MM-DD and YYYY-MM-DDThh:mm:ssZ
  if (preg_match('@^[0-9]{4}-[0-1]?[0-9]-[0-3]?[0-9]$@', $str)) {
    return strftime("%Y-%m-%d", strtotime($str));
  } elseif (preg_match('@^[0-9]{4}-[0-1]?[1-9]-[0-3]?[1-9]T[0-2][0-9]:[0-6]?[0-9]:[0-6]?[0-9]Z$@', $str)) {
    return strftime('%Y-%m-%dT%H:%M:%SZ', strtotime($str));
  } else {
    throw new OAIException(ERR_BAD_ARGUMENT, 'Dates must be formatted YYYY-mm-dd or YYYY-MM-DDThh:mm:ssZ');
  }
}
