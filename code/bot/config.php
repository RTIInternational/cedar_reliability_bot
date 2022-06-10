<?php

//
//
//    M A S T E R    C O N F I G U R A T I O N    F I L E
//
//
//    Review the settings below and make any necessary changes.
//

define ( 'SCRIPT_NAME', 'c3darb0t' );

define ( 'IN_TEST_MODE', FALSE );
define ( 'URL_BASE', 'https://cedar.metadatacenter.org/');
define ( 'URL_LOGOUT', 'https://cedar.metadatacenter.org/logout');
define ( 'URL_CHROME_DRIVER', 'http://0.0.0.0:9222'); // don't forget the listening port

define ( 'DEFAULT_NUMBER_OF_METADATA_ENTRIES_TO_POPULATE', 20);

define ( 'LOG_FILE', './logs/cedarbot.log' );
define ( 'VALUES_FILE_PREFIX', './data/populateData-');
define ( 'VALUES_FILE_SUFFIX', '.csv');
define ( 'VALUES_FILE_FIELD_DELIMITER', ',');

define ( 'PERFORMANCE_LOG_FILE', './logs/cedarbot-run.log');

?>
