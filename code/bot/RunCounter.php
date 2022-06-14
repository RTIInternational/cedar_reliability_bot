<?php

define ( 'COUNT_FILE', './cedarbot-run-count.txt');

class RunCounter {

    public function __construct( ) {
        // Initialize count file, if it doesn't exist.
        if ( !file_exists ( COUNT_FILE ) ) {
            file_put_contents(COUNT_FILE, 0);
        }
    }

    public function getCount() {
        $runCount = 0 ;
        if ( is_readable ( COUNT_FILE )) {
            $contents = file_get_contents(COUNT_FILE);
            if ( is_numeric ($contents))
                $runCount = intval($contents);
        }
        return $runCount;
    }

    public function getPaddedCount() {
        $count = $this->getCount();
        return sprintf('%05d', $count);
    }

    public function incrementCount() {
        if ( is_writable ( COUNT_FILE )) {
            $count = $this->getCount();
            $count++;
            file_put_contents(COUNT_FILE, $count);
        }
    }
}

?>