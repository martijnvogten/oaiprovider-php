<?php 
namespace oaiprovider\xml;

class DublinCoreRecord {
  
  private $fields = array();
  
  function addNS($ns, $tagName, $value) {
    $this->fields[] = array($ns, $tagName, $value);
  }
  
  function add($tagName, $value) {
    $this->addNS('', $tagName, $value);
  }
  
  function toXml() {
    $doc = new \DOMDocument;
    $record = $doc->createElementNS(NS::OAI_DC, "dc");
    $record->setAttributeNS(NS::XML, "xmlns:oai_dc", NS::OAI_DC);
    $record->setAttributeNS(NS::XML, "xmlns:dc", NS::DC);
    $record->setAttributeNS(NS::XSI, "xsi:schemaLocation", "http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd");
    
    foreach($this->fields as $f) {
      appendElement($record, $f[0], $f[1], $f[2]);
    }
    
    return $doc->saveXML($record);
  }
  
}
