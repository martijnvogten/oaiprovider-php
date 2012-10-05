<?php 
namespace oaiprovider\xml;

class EuropeanaRecord {
  
  private $fields = array();
  
  function addNS($ns, $tagName, $value) {
    $this->fields[] = array($ns, $tagName, $value);
  }
  
  function add($tagName, $value) {
    $this->addNS('', $tagName, $value);
  }
  
  function toXml() {
    $doc = new \DOMDocument;
    $record = $doc->createElementNS(NS::ESE, "record");
    $record->setAttributeNS(NS::XML, "xmlns:oai_dc", NS::OAI_DC);
    $record->setAttributeNS(NS::XML, "xmlns:dc", NS::DC);
    $record->setAttributeNS(NS::XSI, "xsi:schemaLocation", "http://www.europeana.eu/schemas/ese/ http://www.europeana.eu/schemas/ese/ESE-V3.3.xsd");
    
    foreach($this->fields as $f) {
      appendElement($record, $f[0], $f[1], $f[2]);
    }
    
    return $doc->saveXML($record);
  }
  
  static function mapESEType($t) {
    $ese_types = array('TEXT','SOUND','IMAGE','VIDEO');
    if (in_array($t, $ese_types, true)) {
      return $t;
    }
    return 'TEXT';
  }
}
