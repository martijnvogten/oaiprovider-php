<?php 

namespace oaiprovider;

// Request parameters
const P_VERB = "verb";
const P_RESUMPTIONTOKEN = "resumptionToken";
const P_IDENTIFIER = "identifier";
const P_METADATAPREFIX = "metadataPrefix";
const P_FROM = "from";
const P_UNTIL = "until";
const P_SET = "set";

// OAI error messages
const ERR_BAD_ARGUMENT = "badArgument";
const ERR_BAD_RESUMPTION_TOKEN = "badResumptionToken";
const ERR_BAD_VERB = "badVerb";
const ERR_CANNOT_DISSEMINATE_FORMAT = "cannotDisseminateFormat";
const ERR_ID_DOES_NOT_EXIST = "idDoesNotExist";
const ERR_NO_RECORDS_MATCH = "noRecordsMatch";
const ERR_NO_METADATA_FORMATS = "noMetadataFormats";
const ERR_NO_SET_HIERARCHY = "noSetHierarchy";

