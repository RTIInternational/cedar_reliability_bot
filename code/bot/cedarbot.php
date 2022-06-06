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

	$loginUsername = $_ENV['LOGIN_USERNAME'];
	$loginPassword = $_ENV['LOGIN_PASSWORD'];
	if ( ! isset ( $loginUsername ) || ! isset ( $loginPassword) )
		throw new \Exception ( 'Login credentials are not set in your .env file. Please check your .env file and make sure that LOGIN_USERNAME and LOGIN_PASSWORD environment settings are defined.');

	$page = $session->getPage();
	wait();
	if ( !str_contains($session->getCurrentUrl(), '/dashboard' ) ) {
		
		$loginForm = $page->find('css','form');
		if ( null === $loginForm )
			throw new \Exception('Login form not found! Session may already be logged in');

		$loginForm->fillField('username', $loginUsername );
		$loginForm->fillField('password', $loginPassword );
		wait();

		$loginForm->submit();
		wait();
	}
	else
		$log->info('Seems already logged in by the looks');
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

	global $session;
	
	// Folder DIVS - this one gives us access to the UPDATED HEAL Study Core Metadata
	$divs = $session->getPage()->findAll('css','div[ng-if="!dc.isFolder(resource)"]');
	$healStudyDiv = null;
	if ( null == $divs )
		throw new \Exception('Unable to access any non-resource folders from Dashboard');
	foreach ( $divs as $div ) {
		$healStudyDiv = $div; // just grab first one
		break;
	}
	if ( null === $healStudyDiv )
		throw new \Exception('Unable to access UPDATED Heal Study Core Metadata tile from Dashboard');

	// Click the Populate option
	$button = $healStudyDiv->find('css','button') ;
	if ( null === $button ) throw new \Exception('Populate option could not be found for UPDATED Heal Study Core Metadata folder'); // flag this

	$ngClick = $button->getAttribute('ng-click');
	if ( "dc.goToResource(resource, 'populate')" === $ngClick) {
		$button->click();
	}
	wait();
}

function populateQuestion ( $fieldSection, &$valuesSubmitted ) {

	global $fields, $log;

	$log->info('Processing for ' . $fieldSection->getText() );

	$word = new Word();

	$question = $fieldSection->find('css','div[ng-click="toggleActive(index)"]');
	if ($question === null) {
		throw new \Exception('Question not found.');
	}
	$questionText = $question->getText() ;
	$question->click(); // opens up field for text entry
	wait(3);

	$form = $fieldSection->find('css','form');

	if ( array_key_exists($questionText, $fields)) {

		$fieldSpecification = $fields[$questionText];

		$inputField = $form->find('css','input');
		$fieldName = $inputField->getAttribute('name');

		$value = '';
		
		if ( $fieldSpecification[0] !== NULL && $fieldSpecification[1] !== NULL ) {
			if ( $fieldSpecification[0] === TEXTFIELD) {
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
			wait(5);
		}
		array_push($valuesSubmitted, $value); // just blindly record values submitted in order
	}
	else {
		throw new \Exception('Field for $questionText was not found in web form');
	}
}

/**
 **
 ** This function take care of entering one metadata Heal Study entry.
 ** Problems encountered during this activity will be reported as a warning.
 **
 */
function populateHealthStudyMetadataEntity($populateCount, $valuesEnteredFile ) {

	global $session, $log;

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
	if ( null !== $editActionsDiv) {
		$saveButton = $editActionsDiv->find('css','button[id="button-save-metadata"]');
		if ( null !== $saveButton) {
			$log->info('Saving Metadata');
			$saveButton->click();
			$dateSaved = date('Y/m/d H:m:s');
		}
	}

	// Store a record of the values populated
	$filePointer = fopen($valuesEnteredFile, 'a');
	$populateCount++;
	fwrite($filePointer, $populateCount . VALUES_FILE_FIELD_DELIMITER . $dateSaved . VALUES_FILE_FIELD_DELIMITER );
	foreach ( $valuesSubmitted as $value ) {
		fwrite($filePointer, $value . VALUES_FILE_FIELD_DELIMITER);
	}
	fwrite ( $filePointer, "\n" );
	fclose ( $filePointer ) ; // housekeep
	wait(10);
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

$log->info( SCRIPT_NAME . ' STARTING');

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

try {

	$log->info("Attempting to populate $numberOfMetadataEntitiesToPopulate HEAL Study Metadata entities");

	checkBrowserIsRunning(); // Check if our browser is running

	$session->start(); // Start browser session
	$session->visit(URL_BASE);
	wait(3);

	cedarLogin(); // Login

	$valuesEnteredFile = VALUES_FILE_PREFIX . date('Y-m-d-Hms') . VALUES_FILE_SUFFIX;

	for ( ; $populateCount < $numberOfMetadataEntitiesToPopulate; $populateCount++) {
		// Navigate to Dashboard
		$session->visit('https://cedar.metadatacenter.org/dashboard?sharing=shared-with-me'); wait();
		accessHealStudy(); // Navigate to the Heal Study Populate page from the Dashboard
		populateHealthStudyMetadataEntity($populateCount, $valuesEnteredFile); // return an array of everything entered
	}
}
catch (Exception $exception ) {
	$log->error( $exception->getMessage() . ' after ' . $populateCount . ' populations' );
}
finally {

	// Wrap up and give a summary.
	if ( ! IN_TEST_MODE )
		cedarLogout(); // regardless of if we have an error condition, let's try to logout (if that doesn't work, we don't care)

	$timeEnd = time();
	$log->info( SCRIPT_NAME . ' ENDING after ' . $populateCount . ' populations.' .
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