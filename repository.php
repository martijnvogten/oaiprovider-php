<?php 
namespace oaiprovider;

interface Repository {
  
  /**
   * Return identify data for this repository in the following form:
   * array(
   *     'repositoryName' => 'Example OAI Provider',
   *     'baseUrl' => 'http://example.com/oai/',
   *     'protocolVersion' => '2.0',
   *     'adminEmail' => 'admin@example.com',
   *     'earliestDatestamp' => '1970-01-01T00:00:00Z',
   *     'deletedRecord' => 'persistent',
   *     'granularity' => 'YYYY-MM-DDThh:mm:ssZ')
   *     
   * @return array of identify data
   */
  function getIdentifyData();
  
  /**
   * Return available metadata formats for this repository in the following form:
   * array(
   *     array(
   *         'metadataPrefix' => 'oai_dc',
   *         'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
   *         'metadataNamespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/',
   *     ),
   *     array(
   *         'metadataPrefix' => 'ese',
   *         'schema' => 'http://www.europeana.eu/schemas/ese/ESE-V3.3.xsd',
   *         'metadataNamespace' => 'http://www.europeana.eu/schemas/ese/',
   *     ),
   * )
   * 
   */
  function getMetadataFormats();
  
  /**
   * Return the sets of this repository in the following form:
   * <code>
   *   array(
   *     array('spec' => 'FICTION' , 'name' => 'All books in the fiction category'),
   *     array('spec' => 'NON_FICTION' , 'name'  => 'All books in the non-fiction category')
   *   );
   * </code>
   * 
   * @return array sets
   */
  function getSets();
  
  /**
   * Return identifiers of records satisfying the criteria.
   * Implementors MUST return records ordered by ascending identifier.
   * 
   * @param $from timestamp if set, return only identifiers of records changed after 
   * this date
   * @param $until timestamp if set, return only identifiers of records changed before 
   * this date
   * @param $set string if set, return only identifiers of records in this set
   * @param $last_identifier mixed if set, return only identifiers of records that are
   * a continuation of the incomplete list ended with this identifier.
   * @param $max_results int if set, return only identifiers of records that are
   * a continuation of the incomplete list ended with this identifier.
   * @return array list of identifiers
   */
  function getIdentifiers($from, $until, $set, $last_identifier, $max_results);
  
  /**
   * @return <code>Header</code> instance for the record with specified identifier
   */
  function getHeader($identifier);
  
  /**
   * @return XML representation of the record in the specified metadataFormat 
   */
  function getMetadata($metadataPrefix, $identifier);
}

/**
 * OAI Header structure returned by <code>getHeader</code>
 */
class Header {
  /**
   * Unique identifier of the record
   */
  public $identifier;
  
  /**
   * Time of last change in seconds since UNIX epoch
   */
  public $datestamp;
  
  /**
   * List of all sets this record is contained in
   */
  public $setSpec = array();
  
  /**
   * <code>true</code> if this record has been deleted
   */
  public $deleted = false;
}
