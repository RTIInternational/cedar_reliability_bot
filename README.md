# CEDAR Reliability Bot
Project to assess CEDAR metadata form reliability for the HEAL Data Ecosystem.

# Overview
The [CEDAR Bot main program](/code/bot/cedarbot.php) has been written in PHP, and harnesses the Mink and Chrome Drivers to control a browser in real-time and direct it to navigate to the CEDAR Metadata Repository and populate HEAL Study Data using the established template.

`Mink` is a wrapper around popular frameworks such as `Selenium` and `Chrome Driver` to provide a more standard and flexible way of either doing browser emulation, or browser controlled interactions, or combinations of both (depending on your needs).

The CEDAR Bot program can be edited with a standard text editor and changed as needed. Because it's PHP and directly parsed and ran at run-time, there is no need to compile this to a binary form. It can be edited and changed and repeatedly run following any change - without additional steps being needed.

# Installation/Setup
## Initial requirements

+ php (7.4.3+)
+ composer
+ Google Chrome browser
+ Operating System

### PHP
PHP version 7.4.3 was used to develop and run this bot. There is no special reason for chosing this version, other than it being the most current version of PHP at the time of development of the bot.

Your operating system may be bundled with PHP already installed. A quick test to see if it's already installed would be to attempt to run `php --version` from your command line. If this successfully runs and displays the version of PHP, it should mean that it is installed correctly.

If it is not installed, you should follow a good guide on the web for installing PHP onto your operating system. It should be accessible from the command line.

For Windows, make sure that the php.exe executable is in the system path. You may need to do this by editing the PATH environment variable on your system and adding the path to this executable.

After the installation, you should be able to run `php --version` from the command line and it should successfully display the version number.

I'm not sure of any specific extensions that may need to be enabled for PHP. This README will be updated if needed accordingly.

### Composer
[Composer](https://en.wikipedia.org/wiki/Composer_(software)) is an application level dependency manager for the PHP programming language. In short, it is used to independently download all of the libraries that your application needs to run successfully. By using Composer, you decouple yourself from the worry and effort associated with individually downloading and making sure you are using the correct libraries your program needs.

Follow the instructions for your operating system to install `composer`.


### Google Chrome Browswer
Google Chrome should be installed on your operating system. Follow the instructions for your operating system for installing Chrome. Please refer to the [Google Chrome Downloads](https://www.google.com/chrome/downloads/) page.

### Operating System
This bot was developed on the Ubuntu operating system (Ubuntu 20.04.4 LTS, code name `focal`).

At this time, no attempt have been made to run this program on another operating system. We expect, however, that the bot
would run correctly on other operating systems, including Windows 10 and Mac OS, provided that they have the same
software tools and commands outlined in the [Initial Requirements](#initial-requirements). Please update these Read Me notes, if you are aware of anyone, including yourself, successfully running this bot on other operating systems.

## Composer update
Once you have `composer` installed correctly, open a command line shell for your operating system.
Navigate to the `code\bot` directory.
Issue the command `composer update`.
You should see messages associated the composer. It should pull down all of the necessary libraries for you to operate the bot.

## Running Chrome as a browser service
Before running the bot itself, you must run the Chrome Browser. It runs as a service to the bot, providing the browser needed to access and drive through CEDAR Metadata repository dashboard to successfully populate CEDAR records/entities. *This script must be running at all times when the bot is running.*

### Linux (and maybe MacOS, needs to be verified)
A script has been provided for this in `/code/bot`. This is the `/code/bot/run-chrome.sh` command.

Open a command line window and run this Bash script.
The Chrome Browser should appear on your screen, with an empty window (provided it is not already running).

### Windows
#### One time setup
1. In chrome, create a new browser user profile. A new profile should be created in `%APPDATA~\Local\Google\Chrome\User Data%`. Cut and paste the location of this new directory.
2. In File Explorer, browse to the `/code/bot` directory. Edit the `/code/bot/run-chrome.bat` script and change the `--user-data-dir` setting to match the location you captured above.
3. Save this batch file.

#### Running
1. Open a new command-line window.
2. Change directory to the location of your `/code/bot` installation.
3. Within this directory, there is a DOS batch script, called `/code/bot/run-chrome.bat`. Type `run-chrome` and hit Enter. You may need to type `start run-chrome.bat`, if you find that `run-chrome` doesn't work.
4. A blank Chrome browser window should appear.


## Running CEDAR metadata bot
### One time setup (for all operating systems)
1. Create a .env file in the `/code/bot` directory (if one doesn't already exist).
2. Edit this file.
3. Manually add new settings into this file.
	i. LOGIN_USERNAME=`<your CEDAR username/email>`
	ii. LOGIN_PASSWORD=`<your CEDAR account password, sorry this is in plain-text>`
	iii. Your CEDAR API Token. To get this, login to CEDAR, and navigate to your profile. It is displayed on this page. Grab the KEY text string and place it in this line.
	iv. Your HEAL DATA STUDY URL can be obtained by navigating to the Workspace in CEDAR (after login). Simply copy and paste this full URL into a single line for this setting.
	v. Your HEAL_SHARED_WITH_ME_URL can be obtained by clicking on the Shared with Me folder. After doing this, copy and paste the full URL into a single line for this setting.
4. Save the contents of this file.
5. In the `/code/bot` directory, create a directory `logs` and `data`. Progress and performance logs will be stored in the `logs` subdirectory. Data submitted and retrieved from the CEDAR API will be stored in the `data` directory.
6. Additionally, you should review the contents of the `/code/bot/config.php` file.
	i. For Windows, in particular, you may need to change the line `define ( 'URL_CHROME_DRIVER', 'http://0.0.0.0:9222'); ` to `http://localhost:9222`. If you change this file, make sure you save it.
	ii. the line `define ( 'DEFAULT_NUMBER_OF_METADATA_ENTRIES_TO_POPULATE', 5);` also defines how many records/entities the bot will try to populate before quitting. This can be increased or decreased according to your preferences. It's suggested to change this to 1, for your first time verifying and operating the bot.
	iii. The count for the number of runs performed is stored in the file `cedarbot-run-count.txt`. Edit this with a text file, if you want to reset this count back to 0. You must specify a numeric value only.
	iv. There is a setting to delay the Save operation, after the form is populated. This is configured by adjusting the setting `WAIT_SECONDS_BEFORE_SUBMIT` in the `config.php` file.

### Linux (and maybe MacOS, needs to be verified)
Once you have Chrome running through the [above script](#running-chrome-as-a-browser-service), you can run the main script for the bot.
This is located within `/code/bot` and it is the script called `/code/bot/run-bot.sh`.
1. Open up a command line shell/window.
2. Navigate to the `/code/bot` directory location.
3. Type `./run-bot.sh` and it should run.

### Windows
Once you have Chrome running through the [above script](#running-chrome-as-a-browser-service), you can run the main script for the bot.
1. Open a DOS command line shell/window.
2. Change directory to the `/code/bot` directory location.
3. Type the command `run-bot` and hit Enter. You may need to type `start run-bot.bat`, if you find that `run-bot` by itself doesn't work.
4. The program should start. You can check the `logs` subdirectory for progress log updates. They will be logged in real-time as the program progresses through.

### Progress checking (all operating systems)
It should log outputs to the window as it runs. It actually logs program progress into `/code/bot/logs/cedarbot.log`.
### Linux (possibly MacOS)
You should be able to see the log messages in real-time, by doing:
1. `cd ./code/bot/logs` <Enter>
2. `tail -f cedarbot.log` <Enter>

### Windows
Windows does not have a native command (that we're aware of) that does continuous logging. There are known utilities that you can get to do this.
You can use programs like Notepad++ however, to open the log file. When you open the file, it will display a snapshot of the log file. Over time, particularly if you navigate away from Notepad++ and come back, it may prompt you to reload the latest log version (as it detects changes to this). If you anwswer Yes, it will reload the log file and you can navigate down to the bottom of the file to see the most current log messages.
