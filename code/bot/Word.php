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

    public function getRandomUrl() {
        $randomWord = $this->getRandomWord();
        return "https://www.$randomWord.org";
    }

    public function getRandomNumber() {
        $randomNumber = rand(0,100000);
        return $randomNumber;
    }

    public function getRandomEmail() {
        $name = $this->getRandomWord();
        $domain = $this->getRandomWord();
        return "$name@$domain.org";
    }

}

?>