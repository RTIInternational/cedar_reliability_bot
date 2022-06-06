#!/bin/bash
echo Starting Google Chrome
google-chrome-stable --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 --disable-extensions 2> /dev/null &
_pid=$!
sleep 10s
echo Starting Cedarbot!
php cedarbot.php
kill -3 $_pid
