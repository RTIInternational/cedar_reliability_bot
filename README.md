# CEDAR Reliability Bot
Project to assess CEDAR metadata form reliability for the HEAL Data Ecosystem.

# Overview
The [CEDAR Bot main program](/code/bot/cedarbot.php) ....

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

After the installation, you should be able to run `php --version` from the command line and it should successfully display the version number.

TBD, not aware of any extensions needed for php. Will verify later, when I attempt to run this on Windows. Ideally, we should find someone who has not setup PHP and have them walk through and document the steps they needed to perform to successfully get the bot running.

### Composer
[Composer][https://en.wikipedia.org/wiki/Composer_(software) is an application level dependency manager for the PHP programming language. In short, it is used to independently download all of the libraries that your application needs to run successfully. By using Composer, you decouple yourself from the worry and effort associated with individually downloading and making sure you are using the correct libraries your program needs.

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
Before running the bot itself, you must run the Chrome Browser. It runs as a service to the bot, providing the browser needed to access and drive through CEDAR Metadata repository dashboard to successfully populate CEDAR records/entities.

A script has been provided for this in `/code/bot`. This is the `/code/bot/run-chrome.sh` command.

Open a command line window and run this Bash script.
The Chrome Browser should appear on your screen, with an empty window (provided it is not already running).

## Running CEDAR metadata bot
Once you have Chrome running through the [above script](#running-chrome-as-a-browser-service), you can run the main script for the bot.

This is located within `/code/bot` and it is the script called `/code/bot/run-bot.sh`.

Open a command line window and run this Bash script.

It should log outputs to the window as it runs. It actually logs program progress into `/code/bot/logs/cedarbot.log` which you can `tail -f` to watch new log entries appear.


