#!/bin/bash
php cedarbot.php &
tail -f ./logs/cedarbot.log
