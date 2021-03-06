<?php
define ('REQUIRED', TRUE);
define ('TEXTFIELD', 1);
define ('RADIO', 2);
define ('EMAIL', 3);
define ('URL', 4);
define ('NUMERIC', 5);
define ('DATETIME', 6);
define ('ID',7);
define ('SELECT', 8);

// Question Text and associated attributes
$fields = array(
    'Study Title or Name' => array(TEXTFIELD,REQUIRED),
    'Study Description or Abstract' => array(TEXTFIELD,REQUIRED),
    'Study Nickname or Alternative Title' => array(TEXTFIELD,REQUIRED),
    'NIH Application ID' => array(ID,REQUIRED),
    'NIH RePORTER Link' => array(URL,REQUIRED),
    'ClinicalTrials.gov Study ID' => array(TEXTFIELD,REQUIRED),
    'Name of Repository' => array(TEXTFIELD,REQUIRED),
    'Study ID assigned by Repository' => array(ID,REQUIRED),
    'Repository-branded Study Persistent Identifier' => array(TEXTFIELD,REQUIRED),
    'Study citation at Repository' => array(TEXTFIELD,REQUIRED),
    'CEDAR Study-level Metadata Template Instance ID' => array(TEXTFIELD,REQUIRED),
    'Other Study-Associated Websites' => array(URL,REQUIRED),
    'Is this study HEAL-funded?' => array(RADIO,REQUIRED),
    'Does this study belong to a study group or collection?' => array(RADIO,REQUIRED),
    'Name of the study group or collection(s) to which this study belongs' => array(TEXTFIELD,REQUIRED),
    'Funder or Grant Agency Name' => array(TEXTFIELD,REQUIRED),
    'Funder or Grant Agency Abbreviation or Acronym' => array(TEXTFIELD,REQUIRED),
    'Funder or Grant Agency Type' => array(SELECT,NULL), // SELECT governmental, non-governmental
    'Funder Geographic Reach' => array(SELECT,NULL), // SELECT
    'Funding or Grant Award ID' => array(TEXTFIELD,REQUIRED),
    'Funding or Grant Award Name' => array(TEXTFIELD,REQUIRED),
    'Investigator First Name' => array(TEXTFIELD,REQUIRED),
    'Investigator Middle Initial' => array(TEXTFIELD,REQUIRED),
    'Investigator Last Name' => array(TEXTFIELD,REQUIRED),
    'Investigator Institutional Affiliation' => array(TEXTFIELD,REQUIRED),
    'Identifier Type' => array(SELECT,NULL), // SELECT - including doi
    'Identifier Value' => array(TEXTFIELD,REQUIRED),
    'Contact First Name' => array(TEXTFIELD,REQUIRED),
    'Contact Middle Initial' => array(TEXTFIELD,REQUIRED),
    'Contact Last Name' => array(TEXTFIELD,REQUIRED),
    'Contact Affiliation' => array(TEXTFIELD,REQUIRED),
    'Contact Email' => array(EMAIL,REQUIRED),
    'Registrant First Name' => array(TEXTFIELD,REQUIRED),
    'Registrant Middle Initial' => array(TEXTFIELD,REQUIRED),
    'Registrant Last Name' => array(TEXTFIELD,REQUIRED),
    'Registrant Affiliation' => array(TEXTFIELD,REQUIRED),
    'Registrant Email' => array(EMAIL,REQUIRED),
    'Will the study collect or produce data?' => array(RADIO,REQUIRED),
    'Will the study make data available?' => array(SELECT,NULL), // SELECT none, some or all
    'Will available data have restrictions on access?' => array(SELECT,NULL), // SELECT none, some or all
    'Has data collection/production started?' => array(SELECT,NULL), // SELECT not started, started, or finished
    'Has data release started?' => array(SELECT,NULL),
    'Date when first data will be collected/produced (Anticipated)' => array(DATETIME,REQUIRED),
    'Date when last data will be collected/produced (Anticipated)' => array(DATETIME,REQUIRED),
    'Date when first data will be released (Anticipated)' => array(DATETIME,REQUIRED),
    'Date when last data will be released (Anticipated)' => array(DATETIME,REQUIRED),
    'Will study produce shareable products other than data?' => array(RADIO,REQUIRED),
    'Primary Publications DOI' => array(TEXTFIELD,REQUIRED),
    'Primary Study Findings' => array(TEXTFIELD,REQUIRED),
    'Secondary Publications DOI' => array(TEXTFIELD,REQUIRED),
    'Study Translational Focus' => array(SELECT,NULL),
    'Types of determinants/mechanisms the study is investigating' => array(SELECT,NULL),
    'Category or Type/Stage of Study Research' => array(SELECT,NULL),
    'Is the study conducting primary or secondary research?' => array(SELECT,NULL),
    'Is the study conducting observational or experimental research?' => array(SELECT,NULL),
    'Study Subject Type' => array(SELECT,NULL),
    'Study Type/Design' => array(SELECT,NULL),
    'Treatment Investigation Stage or Type' => array(NULL,NULL),
    'Treatment Mode' => array(NULL,NULL),
    'Treatment Novelty' => array(NULL,NULL),
    'Is the Treatment given/applied to individuals or populations?' => array(NULL,NULL),
    'Treatment Type' => array(NULL,NULL),
    'Relevant Opioid use and/or Pain condition - Category(ies)' => array(NULL,NULL),
    'Opioid use and/or Pain condition - Investigation Stage or Type' => array(NULL,NULL),
    'Pain - Causal condition' => array(NULL,NULL),
    'Pain - Study treatment or target condition is causal condition or pain?' => array(NULL,NULL),
    'Study treatment or target condition - Detail' => array(NULL,NULL),
    'Study outcome condition - Detail' => array(NULL,NULL),
    'Other measured or tracked conditions - Detail' => array(NULL,NULL),
    'To humans of which gender identity(ies) do study results apply?' => array(NULL,NULL),
    'To humans of which sexual identity(ies) do study results apply?' => array(NULL,NULL),
    'To humans of which age/developmental stage do study results apply?' => array(NULL,NULL),
    'To humans in which special vulnerability categories do study results apply?' => array(NULL,NULL),
    'To humans of which age/developmental stage do study results apply?' => array(NULL,NULL),
    'Is data quantitative or qualitative?' => array(NULL,NULL),
    'Source of Data' => array(NULL,NULL),
    'Data Type' => array(NULL,NULL),
    'Human Subject Data - Unit of Collection' => array(NULL,NULL),
    'Human Subject Data - Expected Number of the Unit of Collection' => array(NUMERIC,REQUIRED),
    'Human Subject Data - Unit of Analysis' => array(NULL,NULL),
    'Human Subject Data - Expected Number of the Unit of Analysis' => array(NUMERIC,REQUIRED),
    'Human Subject Data - Individual or Aggregated Data made available?' => array(NULL,NULL),
    'Human Subject Geographic Data - Collected at what level of detail?' => array(NULL,NULL),
    'Human Subject Geographic Data - Available at what level of detail?' => array(NULL,NULL),
);

?>