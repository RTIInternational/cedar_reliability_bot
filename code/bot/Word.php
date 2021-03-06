<?php

class Word {

    public array $words;

    public function __construct() {
        $wordsFile = './words.txt';
        $this->words = file($wordsFile);
    }

    public function getRandomWord() {
        $numberOfLines = count($this->words);
        $lastArrayIndex  = $numberOfLines - 1;
        $randomIndex = rand(0, $lastArrayIndex);
        $randomText = $this->words[$randomIndex];
        $randomText = preg_replace("/[^A-Za-z0-9 ]/", '', $randomText); // remove non-alphanumeric characters
        return $randomText;
    }

    public function getRandomCompoundWords( $numberOfWords=2) {
        $returnWords = '';
        for ($wordCount = 0 ; $wordCount < $numberOfWords; $wordCount++) {
            if ( strlen($returnWords) > 0 ) $returnWords .= ' '; // add spacing
            $returnWords .= $this->getRandomWord();

        }
        return $returnWords;
    }

    public function getRandomUrl() {
        $randomWord = $this->getRandomWord();
        return strtolower("https://www.$randomWord.org");
    }

    public function getRandomNumber() {
        $randomNumber = rand(0,100000);
        return $randomNumber;
    }

    public function getRandomEmail() {
        $name = $this->getRandomWord();
        $domain = $this->getRandomWord();
        return strtolower("$name@$domain.org");
    }

    function getRandomId() {
        $prefix = rand(1,999);
        $middle = rand(1,999);
        $suffix = rand(1,999);
        $number = sprintf('%03d', $prefix) . '-' . sprintf('%03d', $middle) . '-' . sprintf('%03d', $suffix);
        return $number;
    }

}

?>