<?php

/**
 *	C E D A R    M E T A D A T A    T E M P L A T E    P O P U L A T I O N    B O T
 * 
 *	This script uses Behat/Mink drivers to activate a session with Cedar, login, open up the Dashboard
 *	and populate a metadata template.
 *
 *	@author Dean Jackman <djackman@rti.org>, with credit to others in the RTI International HEAL Data Stewards Team
 *
 *	@since 0.0.1	Initial version
 *
 */

require_once 'vendor/autoload.php';
require_once 'config.php';

require_once 'Word.php';
require_once 'Fields.php';
require_once 'RunCounter.php';

use Behat\Mink\Mink;
use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


//
//
//    H E L P E R    F U N C T I O N S
//
//


// TBD check if there is an actual wait condition for the browser driver
function wait($seconds=3) {
	sleep($seconds);
}

function checkBrowserIsRunning() {

	// TBD

}

function cedarLogin() {

	global $session, $log;

	$log->debug('Logging in');

	$loginUsername = $_ENV['LOGIN_USERNAME'];
	$loginPassword = $_ENV['LOGIN_PASSWORD'];
	if ( ! isset ( $loginUsername ) || ! isset ( $loginPassword) )
		throw new \Exception ( 'Login credentials are not set in your .env file. Please check your .env file and make sure that LOGIN_USERNAME and LOGIN_PASSWORD environment settings are defined.');

	$page = $session->getPage();
	wait();
	if ( !str_contains($session->getCurrentUrl(), '/dashboard' ) ) {
		
		$loginForm = $page->find('css','form');
		if ( NULL == $loginForm )
			throw new \Exception('Login form not found! Session may already be logged in');

		$loginForm->fillField('username', $loginUsername );
		$loginForm->fillField('password', $loginPassword );
		wait();

		$loginForm->submit();
		$log->debug('Login submitted');
		wait();
	}
	else
		$log->info('Looks like we could already be logged in, so just proceeding.');
}

function cedarLogout() {
	global $session, $log;
	
	try {

		$session->visit( URL_LOGOUT );
		$log->debug( 'Logged out of session' );

	}
	catch ( Exception $ignored ) {
		// since we may have experienced an error condition leading up to this point, simply
		// attempt a logout, but don't lose sleep if it doesn't work.
	}
}

function accessHealStudy() {

	global $session, $log;

	$log->debug('Accessing Heal Study from Dashboard');

	// Folder DIVS - this one gives us access to the UPDATED HEAL Study Core Metadata
	$healStudyDiv = NULL;
	$divs = $session->getPage()->findAll('css','div[ng-if="!dc.isFolder(resource)"]'); // Look for all non folder resources
	if ( NULL == $divs )
		throw new \Exception('Unable to access any non-resource folders from Dashboard');
	foreach ( $divs as $div ) {
		// Look for the UPDATED HEAL Study Core Metadata resource by name
		$resource = $div->findAll('css','div[uib-tooltip="UPDATED HEAL Study Core Metadata"]'); // this may break, if they change the name
		if ( null != $resource) {
			$log->debug('Found our HEAL Study option in our Dashboard');
			$healStudyDiv = $div;
			break;
		}
	}
	if ( NULL === $healStudyDiv )
		throw new \Exception('Unable to find our UPDATED Heal Study Core Metadata tile in our Dashboard');

	// Click the Populate option
	$button = $healStudyDiv->find('css','button') ;
	if ( NULL === $button ) throw new \Exception('Populate option could not be found for UPDATED Heal Study Core Metadata folder'); // flag this

	$ngClick = $button->getAttribute('ng-click');
	if ( NULL == $ngClick ) {
		throw new \Exception("Unable to get to populate option from UPDATED Heal Study Core Metadata");
	}
	if ( "dc.goToResource(resource, 'populate')" === $ngClick) {
		$button->click(); // does this return anything - check TBD and put a check and error log here
		$log->debug('Clicked populate button');
	}
	else {
		$log->error('Could not find Populate option');
	}
	wait();
}

function populateQuestion ( $fieldSection, &$valuesSubmitted ) {

	global $fields, $log, $runCounter;

	//$log->info('Processing for ' . $fieldSection->getText() );

	$word = new Word();

	$question = $fieldSection->find('css','div[ng-click="toggleActive(index)"]');
	if ( NULL == $question ) {
		throw new \Exception('Question not found.');
	}
	$questionText = $question->getText() ;
	$question->click(); // opens up field for text entry
	wait();

	$form = $fieldSection->find('css','form');
	if ( NULL == $form) {
		throw new \Exception('Was not able get form');
	}

	if ( array_key_exists($questionText, $fields)) {

		$fieldSpecification = $fields[$questionText];

		$inputField = $form->find('css','input');
		$fieldName = $inputField->getAttribute('name');

		$log->debug('Populating question [' . $questionText . ']' );

		$value = NULL;

		if ( $fieldSpecification[0] !== NULL && $fieldSpecification[1] !== NULL ) {
			if ( $fieldSpecification[0] === TEXTFIELD) {
				if ( 'Study Title or Name' === $questionText ) {
					$value = $word->getRandomCompoundWords(3); // Get a nice longer title
					// add a count to study title to cross reference it to the sets of data submitted and retrieved via API
					$value .= ' [' . $runCounter->getPaddedCount() . ']';
				}
				else if ( 'Study Description or Abstract' == $questionText ) {
					$numberOfWords = rand(1, 30); // just enter random number of words (up to the max limit)
					$value = $word->getRandomCompoundWords($numberOfWords);
				}
				else
					$value = $word->getRandomWord();
				$form->fillField('textField', $value);
			}
			else if ( $fieldSpecification[0] == URL) {
				$value = $word->getRandomUrl();
				$form->fillField('urlField',$value);
			}
			else if ( $fieldSpecification[0] == DATETIME ) {
				$value = date('m/d/Y');
				$form->fillField('dateTimeField', $value);

			}
			else if ( $fieldSpecification[0] == NUMERIC ) {
				$value = $word->getRandomNumber();
				$form->fillField('numericField', $value);
			}
			else if ( $fieldSpecification[0] == EMAIL ) {
				$value = $word->getRandomEmail();
				$form->fillField('emailField', $value);
			}
			else if ( $fieldSpecification[0] == ID) {
				$value = $word->getRandomId();
				$form->fillField('textField',$value);
			}
			else if ( $fieldSpecification[0] == RADIO ) {
				//$log->debug('Got radio button ' . $questionText );
				$radios = $form->findAll('css','input[type="radio"]');
				$value = '';
				if ( NULL != $radios ) { // just make a random choice
					$numberOfOptions = count($radios);
					$randomChoiceClick = rand(1, $numberOfOptions);
					//$log->debug('there are ' . $numberOfOptions . ' choices and we will choose ' . $randomChoiceClick);
					$choiceNumber = 1;
					foreach ( $radios as $radio ) {
						if ( NULL != $radio ) {
							//$log->debug('Considering whether to choose option ' . $choiceNumber);
							if ( $choiceNumber == $randomChoiceClick ) {
								$radio->click();
								$value = $radio->getAttribute('value');
								//$log->debug('Made random choice - ' . $choiceNumber . ' and we are recording the submitted value as [' . $value . ']');
								wait();
								break; // stop looping through, we made our choice
							}
						}
						$choiceNumber++;
					}
				}
			}
			wait();
		}
		$valuesSubmitted[$questionText] = $value; // store into array using question text as key
	}
	else {
		throw new \Exception("Exception caught populating  for [$questionText]");
	}
}

/**
 **
 ** This function take care of entering one metadata Heal Study entry.
 ** Problems encountered during this activity will be reported as a warning.
 **
 */
function populateHealthStudyMetadataEntity($populateCount, $valuesEnteredFile ) {

	global $session, $log, $runCounter;

	$log->debug('Populating Questions');

	$valuesSubmitted = array();

	// Progress through the fields and enter values
	$fieldSections = $session->getPage()->findAll('css','div[ng-if="!isSectionBreak()"]');
	foreach ( $fieldSections as $fieldSection ) {
		populateQuestion ( $fieldSection, $valuesSubmitted ) ;
	}
	//
	// Click Save Metadata button
	//
	$editActionsDiv = $session->getPage()->find('css','div[class="edit-actions"]');
	$dateSaved = NULL;
	if ( NULL != $editActionsDiv) {
		$saveButton = $editActionsDiv->find('css','button[id="button-save-metadata"]');
		if ( NULL != $saveButton) {
			$log->info("Waiting " . WAIT_SECONDS_BEFORE_SUBMIT . " seconds before doing Save");
			wait(WAIT_SECONDS_BEFORE_SUBMIT);
			$log->info('Saving Metadata');
			$saveButton->click();
			$dateSaved = date('Y/m/d H:m:s');
		}
		else {
			$log->error('Could not find Save Button');
		}
	}
	else {
		$log->error('Could not find Edit Actions');
	}
	$valuesSubmitted['saveDate'] = $dateSaved; // added so we can track down the record when we call the API to retrieve records

	// Store a record of the values populated - we might be beter to simply save this as a JSON instead of .csv file
	$filePointer = fopen($valuesEnteredFile, 'a');
	$populateCount++;
	foreach ( $valuesSubmitted as $key => $value ) {
		fwrite($filePointer, $key . VALUES_FILE_FIELD_DELIMITER);
	}
	fwrite($filePointer, "\n" );
	foreach ( $valuesSubmitted as $key => $value ) {
		if ( NULL == $value  ) $value = '';
		fwrite($filePointer, $value . VALUES_FILE_FIELD_DELIMITER);
	}
	fwrite ( $filePointer, "\n" );
	fclose ( $filePointer ) ; // housekeep
	wait(10);

	return $valuesSubmitted;
}

/**
 * This is the function where we do our definitive "did it work" audit. Regardless of what was returned at other points.
 */
function auditValuesSubmitted ($valuesSubmitted=NULL ) {

	global $log, $runCounter;

	$log->debug('Starting Audit');

	if ( isset ( $valuesSubmitted )) {

		$records = getMetadataRecords();
		$recordFound = FALSE;
		foreach ( $records['resources'] as $resource) {
		
			if ( $resource['resourceType'] == 'instance' ) {

				$metadataInstance = getMetadataResource($resource);

				$apiStudyName = $metadataInstance['Minimal Info']['study_name']['@value'];
				$submittedStudyName = $valuesSubmitted['Study Title or Name'];
				$log->debug('Comparing ' . $apiStudyName . ' to ' . $submittedStudyName );

				if($apiStudyName === $submittedStudyName) {
					$recordFound = TRUE;
					$allMatches = compareFields ( $metadataInstance, $valuesSubmitted);
					if ( $allMatches ) {
						$log->info( "Audit - Test Run " . $runCounter->getCount() . " - All fields match");
					}
					else {
						$log->info( "Audit - Test Run " . $runCounter->getCount() . " - Mismatch found!");
					}
				}
				else $log->info( $runCounter->getCount() . " - Did not find a match");

				$results = print_r($metadataInstance, true);

				$storeFile = './data/metadataRecord-' . $runCounter->getPaddedCount() . '-' . date('Y-m-d-His') . '.txt';
				file_put_contents($storeFile, $results);
		
				wait();

				if ( $recordFound ) break; // we found the record, no need to loop through any more
			}
		}
		if ( ! $recordFound ) {
			$log->error($runCounter->getCount() . ' - Record was not found');
		}
	}
	else {
		$log->error( $runCounter->getCount() . ' - Could not perform audit - no values were submitted. Something went wrong!');
	}
}

function getMetadataRecords () {

	global $log;

	$responseArray=NULL;

	$url = $_ENV['HEAL_DATA_STUDY_URL'];

	$request = curl_init ( $url ) ;
	curl_setopt ( $request, CURLOPT_HTTPHEADER, array('Accept: application/json','Authorization: apiKey ' . $_ENV['CEDAR_API_TOKEN'] ) );
	curl_setopt ( $request, CURLOPT_FOLLOWLOCATION, true ) ;
	curl_setopt ( $request, CURLOPT_RETURNTRANSFER, true ) ;

	curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt( $request, CURLOPT_HTTPGET, true);

	$response = curl_exec ($request) or die ("Unable to get $url") ; // Dies if the request doesn't work
	$info = curl_getinfo($request);

	if ( curl_errno($request) || 200 !== curl_getinfo($request, CURLINFO_HTTP_CODE))
		throw new \Exception('HTTP Error experienced getting Metadata entry - ' . curl_getinfo($request, CURLINFO_HTTP_CODE));


	curl_close($request); // close request and free up resources (good housekeeping)

	// Convert the JSON data into a standard array, so we can more easily extract fields from it (Step 2)
	$responseArray = json_decode($response,true);

	return $responseArray;

}

function getMetadataResource($resource) {

	$responseArray = NULL;

	$id = $resource['@id'] ;
	$schemaName = $resource['schema:name'];
	if ( isset ( $id ) && isset( $schemaName )) {
	
		$url = "https://resource.metadatacenter.org/template-instances/" . urlencode($id) ;
		//echo "using [$url]\n" ;

		$request = curl_init ( $url ) ;
		curl_setopt ( $request, CURLOPT_HTTPHEADER, array('Accept: application/json','Authorization: apiKey ' . $_ENV['CEDAR_API_TOKEN'] ) );
		curl_setopt ( $request, CURLOPT_FOLLOWLOCATION, true ) ;
		curl_setopt ( $request, CURLOPT_RETURNTRANSFER, true ) ;

		curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt( $request, CURLOPT_HTTPGET, true);

		$response = curl_exec ($request) or die ("Unable to get $url") ; // Dies if the request doesn't work
		$info = curl_getinfo($request);

		if ( curl_errno($request) || 200 !== curl_getinfo($request, CURLINFO_HTTP_CODE))
			throw new \Exception('HTTP Error experienced getting Metadata Resource - ' . curl_getinfo($request, CURLINFO_HTTP_CODE));

		curl_close($request); // close request and free up resources (good housekeeping)
		
		$responseArray = json_decode($response,true);
	}
	return $responseArray;

}

function compareFields ( $metadataInstance, $valuesSubmitted) {
	$allMatching = FALSE;

	$apiStudyName = $metadataInstance['Minimal Info']['study_name']['@value'];
	$submittedStudyName = $valuesSubmitted['Study Title or Name'];

	if (
		compareField ( 'Study Description or Abstract',
						$metadataInstance['Minimal Info']['study_description']['@value'],
						$valuesSubmitted['Study Description or Abstract'] ) &&
		compareField ( 'Study Nickname or Alternative Title',
						$metadataInstance['Minimal Info']['study_nickname']['@value'],
						$valuesSubmitted['Study Nickname or Alternative Title']) &&
		compareField ( 'NIH Application ID',
						$metadataInstance['Metadata Location Updated']['Metadata Location - Details']['nih_application_id']['@value'],
						$valuesSubmitted['NIH Application ID']) &&
		compareField ( 'NIH RePORTER Link',
						$metadataInstance['Metadata Location Updated']['Metadata Location - Details']['nih_reporter_link']['@id'],
						$valuesSubmitted['NIH RePORTER Link']) &&
		compareField ( 'ClinicalTrials.gov Study ID',
						$metadataInstance['Metadata Location Updated']['Metadata Location - Details']['clinical_trials_study_id']['@value'],
						$valuesSubmitted['ClinicalTrials.gov Study ID']) &&
		compareField ( 'Name of Repository',
						$metadataInstance['Metadata Location Updated']['Data Repositories'][0]['repository_name']['@value'],
						$valuesSubmitted['Name of Repository']) &&
		compareField ('Study ID assigned by Repository',
						$metadataInstance['Metadata Location Updated']['Data Repositories'][0]['repository_study_ID']['@value'],
						$valuesSubmitted['Study ID assigned by Repository']) &&
		compareField ( 'Repository-branded Study Persistent Identifier',
						$metadataInstance['Metadata Location Updated']['Data Repositories'][0]['repository_persistent_ID']['@value'],
						$valuesSubmitted['Repository-branded Study Persistent Identifier']) &&
		compareField ( 'Study citation at Repository',
						$metadataInstance['Metadata Location Updated']['Data Repositories'][0]['repository_citation']['@value'],
						$valuesSubmitted['Study citation at Repository']) &&
		compareField ( 'CEDAR Study-level Metadata Template Instance ID',
						$metadataInstance['Metadata Location Updated']['cedar_study_level_metadata_template_instance_ID']['@value'],
						$valuesSubmitted['CEDAR Study-level Metadata Template Instance ID']) &&
		compareField ( 'Other Study-Associated Websites',
						$metadataInstance['Metadata Location Updated']['other_study_websites'][0]['@id'],
						$valuesSubmitted['Other Study-Associated Websites']) &&
		compareField ( 'Is this study HEAL-funded?',
						$metadataInstance['Citation']['heal_funded_status']['@value'],
						$valuesSubmitted['Is this study HEAL-funded?'] ) &&
		compareField ( 'Does this study belong to a study group or collection?',
						$metadataInstance['Citation']['study_collection_status']['@value'],
						$valuesSubmitted['Does this study belong to a study group or collection?'] ) &&
		compareField ( 'Name of the study group or collection(s) to which this study belongs',
						$metadataInstance['Citation']['study_collections'][0]['@value'],
						$valuesSubmitted['Name of the study group or collection(s) to which this study belongs']) &&
		compareField ( 'Funder or Grant Agency Name',
						$metadataInstance['Citation']['Funding'][0]['funder_name'][0]['@value'],
						$valuesSubmitted['Funder or Grant Agency Name']) &&
		compareField ( 'Funder or Grant Agency Abbreviation or Acronym',
						$metadataInstance['Citation']['Funding'][0]['funder_abbreviation'][0]['@value'],
						$valuesSubmitted['Funder or Grant Agency Abbreviation or Acronym']) &&
		compareField ( 'Funding or Grant Award ID',
						$metadataInstance['Citation']['Funding'][0]['funding_award_id']['@value'],
						$valuesSubmitted['Funding or Grant Award ID']) &&
		compareField ( 'Funding or Grant Award Name',
						$metadataInstance['Citation']['Funding'][0]['funding_award_name']['@value'],
						$valuesSubmitted['Funding or Grant Award Name']) &&
		compareField ( 'Investigator First Name',
						$metadataInstance['Citation']['Investigators'][0]['investigator_first_name']['@value'],
						$valuesSubmitted['Investigator First Name']) &&
		compareField ( 'Investigator Middle Initial',
						$metadataInstance['Citation']['Investigators'][0]['investigator_middle_initial']['@value'],
						$valuesSubmitted['Investigator Middle Initial']) &&
		compareField ( 'Investigator Last Name',
						$metadataInstance['Citation']['Investigators'][0]['investigator_last_name']['@value'],
						$valuesSubmitted['Investigator Last Name']) &&
		compareField ( 'Investigator Institutional Affiliation',
						$metadataInstance['Citation']['Investigators'][0]['investigator_affiliation']['@value'],
						$valuesSubmitted['Investigator Institutional Affiliation']) &&
		compareField ( 'Identifier Value',
						$metadataInstance['Citation']['Investigators'][0]['Investigator Identifiers'][0]['investigator_ID_value']['@value'],
						$valuesSubmitted['Identifier Value']) &&
		compareField ( 'Contact First Name',
						$metadataInstance['*Contacts and Registrants']['Contacts']['contact_first_name']['@value'],
						$valuesSubmitted['Contact First Name']) &&
		compareField ( 'Contact Middle Initial', 
						$metadataInstance['*Contacts and Registrants']['Contacts']['contact_middle_initial']['@value'],
						$valuesSubmitted['Contact Middle Initial']) &&
		compareField ( 'Contact Last Name',
						$metadataInstance['*Contacts and Registrants']['Contacts']['contact_last_name']['@value'],
						$valuesSubmitted['Contact Last Name']) &&
		compareField ( 'Contact Affiliation',
						$metadataInstance['*Contacts and Registrants']['Contacts']['contact_affiliation']['@value'],
						$valuesSubmitted['Contact Affiliation']) &&
		compareField ( 'Contact Email',
						$metadataInstance['*Contacts and Registrants']['Contacts']['contact-email']['@value'],
						$valuesSubmitted['Contact Email']) &&
		compareField ( 'Registrant First Name',
						$metadataInstance['*Contacts and Registrants']['Registrants']['registrant_first_name']['@value'],
						$valuesSubmitted['Registrant First Name']) &&
		compareField ( 'Registrant Middle Initial',
						$metadataInstance['*Contacts and Registrants']['Registrants']['registrant_middle_initial']['@value'],
						$valuesSubmitted['Registrant Middle Initial']) &&
		compareField ( 'Registrant Last Name',
						$metadataInstance['*Contacts and Registrants']['Registrants']['registrant_last_name']['@value'],
						$valuesSubmitted['Registrant Last Name']) &&
		compareField ( 'Registrant Affiliation',
						$metadataInstance['*Contacts and Registrants']['Registrants']['registrant_affiliation']['@value'],
						$valuesSubmitted['Registrant Affiliation']) &&
		compareField ( 'Registrant Email',
						$metadataInstance['*Contacts and Registrants']['Registrants']['registrant_email']['@value'],
						$valuesSubmitted['Registrant Email']) &&
		compareField ( 'Will the study collect or produce data?',
						$metadataInstance['Data Availability']['produce_data']['@value'],
						$valuesSubmitted['Will the study collect or produce data?']) &&
		compareField ( 'Primary Publications DOI',
						$metadataInstance['Findings']['primary_publications'][0]['@value'],
						$valuesSubmitted['Primary Publications DOI'] ) &&
		compareField ( 'Primary Study Findings',
						$metadataInstance['Findings']['primary_study_findings'][0]['@value'],
						$valuesSubmitted['Primary Study Findings'])	&&				
		compareField ( 'Secondary Publications DOI',
						$metadataInstance['Findings']['secondary_publications'][0]['@value'],
						$valuesSubmitted['Secondary Publications DOI'] ) &&
		compareDateField ( 'Date when first data will be collected/produced (Anticipated)',
						$metadataInstance['Data Availability']['data_collection_start_date']['@value'],
						$valuesSubmitted['Date when first data will be collected/produced (Anticipated)']) &&
		compareDateField ( 'Date when last data will be collected/produced (Anticipated)',
						$metadataInstance['Data Availability']['data_collection_finish_date']['@value'],
						$valuesSubmitted['Date when last data will be collected/produced (Anticipated)']) &&
		compareDateField ( 'Date when first data will be released (Anticipated)',
						$metadataInstance['Data Availability']['data_release_start_date']['@value'],
						$valuesSubmitted['Date when first data will be released (Anticipated)']) &&
		compareDateField ( 'Date when last data will be released (Anticipated)',
						$metadataInstance['Data Availability']['data_release_finish_date']['@value'],
						$valuesSubmitted['Date when last data will be released (Anticipated)']) &&
		compareField ( 'Will study produce shareable products other than data?',
						$metadataInstance['Data Availability']['produce_other']['@value'],
						$valuesSubmitted['Will study produce shareable products other than data?'] ) &&
		compareField ( 'Human Subject Data - Expected Number of the Unit of Collection',
						$metadataInstance['Data']['subject_data_unit_of_collection_expected_number']['@value'],
						$valuesSubmitted['Human Subject Data - Expected Number of the Unit of Collection']) &&
		compareField ( 'Human Subject Data - Expected Number of the Unit of Analysis',
						$metadataInstance['Data']['subject_data_unit_of_analysis_expected_number']['@value'],
						$valuesSubmitted['Human Subject Data - Expected Number of the Unit of Analysis'])
		)
		$allMatching = TRUE;

	return $allMatching;
}

function compareField ( $label, $apiField, $submittedField ) {

	global $log;

	$returnMatching = FALSE;

	$log->debug('Comparing ' . $label );

	if ( isset($submittedField )) {
		if ( $submittedField != $apiField ) {
			$log->error($label . " field does not match [Submitted '" . $submittedField . "', Value Retrieved '" . $apiField ."')");
		}
		else
			$returnMatching = TRUE;
	}

	return $returnMatching;

}

function compareDateField ( $label, $apiField, $submittedField ) {
	global $log;

	$returnMatching = FALSE;

	$log->debug('Comparing ' . $label );

	if (isset($submittedField)) {
		if ( isset($apiField) ) {
				$submittedDate = \DateTime::createFromFormat('m/d/Y', $submittedField);
				$apiDate = \DateTime::createFromFormat('Y-m-d', $apiField);
				if ( $submittedDate == $apiDate)
					$returnMatching = TRUE;
				else
					$log->error($label . " Datefield does not match [Submitted '" . $submittedDate->format('Y-m-d') . "', Value Retrieved '" . $apiDate->format('Y-m-d') ."')");
		}
	}
	return $returnMatching;
}


//
//
//    M A I N    P R O C E S S I N G    S T A R T
//
//

// Initialize
date_default_timezone_set('America/New_York');
$timeStart = time();
$timeEnd = NULL;

$log = new Logger( SCRIPT_NAME );
$log->pushHandler ( new StreamHandler( LOG_FILE ));

$log->info( SCRIPT_NAME . ' started');

$performanceLog = new Logger ( SCRIPT_NAME . ' - performance');
$performanceLog->pushHandler ( new StreamHandler ( PERFORMANCE_LOG_FILE));

$performanceLog->info( SCRIPT_NAME . ' STARTING');

$driver = new ChromeDriver(URL_CHROME_DRIVER, null, URL_BASE );
$mink = new Mink(array(
	'browser' => new Session($driver)
));

$mink->setDefaultSessionName( 'browser' );
$session = $mink->getSession();

$populateCount = 0 ;
$numberOfMetadataEntitiesToPopulate = DEFAULT_NUMBER_OF_METADATA_ENTRIES_TO_POPULATE;

$dotEnv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotEnv->load();

$runCounter = new RunCounter();

try {

	$log->info("Attempting to populate $numberOfMetadataEntitiesToPopulate HEAL Study Metadata entities");

	checkBrowserIsRunning(); // Check if our browser is running

	$session->start(); // Start browser session
	$session->visit(URL_BASE);
	wait(3);

	cedarLogin(); // Login

	for ( ; $populateCount < $numberOfMetadataEntitiesToPopulate; $populateCount++) {
		// Navigate to Dashboard
		$session->visit($_ENV['HEAL_SHARED_WITH_ME_URL']); // set this in your .env
		
		wait(5);
		accessHealStudy(); // Navigate to the Heal Study Populate page from the Dashboard
		$runCounter->incrementCount();
		$valuesEnteredFile = VALUES_FILE_PREFIX . $runCounter->getPaddedCount() . '-' . date('Y-m-d-Hms') . VALUES_FILE_SUFFIX;
		$valuesSubmitted = populateHealthStudyMetadataEntity($populateCount, $valuesEnteredFile); // return an array of everything entered
		auditValuesSubmitted ( $valuesSubmitted );
		wait();
	}
}
catch (Exception $exception ) {
	$log->error( $exception->getMessage() . ' after ' . $populateCount . ' populations' );
	$performanceLog->error($exception->getMessage() . ' after ' . $populateCount . ' populations' );
}
finally {

	// Wrap up and give a summary.
	if ( ! IN_TEST_MODE )
		cedarLogout(); // regardless of if we have an error condition, let's try to logout (if that doesn't work, we don't care)

	$timeEnd = time();
	$performanceLog->info( SCRIPT_NAME . ' ENDING after ' . $populateCount . ' populations.' .
				' Processing start: ' . date('Y-m-d H:i:s', $timeStart ) .
				', end: ' . date('Y-m-d H:i:s', $timeEnd) .
				', elapsed duration: ' . ($timeEnd - $timeStart) . ' seconds' );

	if ( $populateCount === $numberOfMetadataEntitiesToPopulate ) {
		$log->info('Expected number of population cycles was detected (' . $populateCount . ')');
	}
	else {
		$log->error('Unexpected end of population cycle detected. ' . SCRIPT_NAME . ' was asked to do ' . $numberOfMetadataEntitiesToPopulate . ' but only did ' . $populateCount . '. Please investigate' );
	}

	if ( IN_TEST_MODE) wait(300); // pause a bit at the end, to give someone a chance to study the browser session

}

?>