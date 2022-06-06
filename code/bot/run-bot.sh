#!/bin/bash
touch cedarbot.log
php cedarbot.php &
tail -f cedarbot.log
