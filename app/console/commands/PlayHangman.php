<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class playHangman extends Command
{
    // Command signature
    protected $signature = 'play:hangman';

    // Command description
    protected $description = 'Play a game of Hangman';

    // Maximum number of incorrect guesses before the player is "hanged"
    protected $maxTries = 6;

    public function __construct()
    {
        parent::__construct();
    }

    // Main logic of the game
    public function handle()
    {
        do {
            $this->info('Welcome to Hangman!');

            // 1. Get the secret word from Player 1
            $secretWord = $this->askSecretWord();

            // 2. Start the guessing process
            $this->playGame($secretWord);

            // 3. Ask if the player wants to play again
        } while ($this->confirm('Do you want to play again?'));
    }

    // Get the secret word from Player 1
    private function askSecretWord()
    {
        $this->info('Player 1: Please enter a secret word.');
        return strtolower($this->secret('Secret Word'));
    }

    // Core game logic
    private function playGame($secretWord)
    {
        $guessedLetters = [];
        $wrongGuesses = 0;

        while (true) {
            $this->displayWord($secretWord, $guessedLetters);

            // 3. Get a letter guess from Player 2
            $guess = strtolower($this->ask('Player 2: Enter a letter'));

            if (in_array($guess, $guessedLetters)) {
                $this->info("You already guessed '$guess'. Try again.");
                continue;
            }

            $guessedLetters[] = $guess;

            if (strpos($secretWord, $guess) === false) {
                $wrongGuesses++;
                $this->warn("Wrong guess! You have " . ($this->maxTries - $wrongGuesses) . " tries left.");
            }

            if ($this->isWordGuessed($secretWord, $guessedLetters)) {
                $this->info('Congratulations! You guessed the word!');
                $this->logResult($secretWord, $guessedLetters, 'Win');
                break;
            }

            if ($wrongGuesses >= $this->maxTries) {
                $this->error('You lost! The word was: ' . $secretWord);
                $this->logResult($secretWord, $guessedLetters, 'Lose');
                break;
            }
        }
    }

    // Display the current state of the word with guessed letters
    private function displayWord($word, $guessedLetters)
    {
        $display = '';

        foreach (str_split($word) as $letter) {
            if (in_array($letter, $guessedLetters)) {
                $display .= $letter . ' ';
            } else {
                $display .= '_ ';
            }
        }

        $this->info($display);
    }

    // Check if the player has successfully guessed the word
    private function isWordGuessed($word, $guessedLetters)
    {
        foreach (str_split($word) as $letter) {
            if (!in_array($letter, $guessedLetters)) {
                return false;
            }
        }

        return true;
    }

    // Log the game results to a file
    private function logResult($word, $guessedLetters, $result)
    {
        $logData = [
            'word' => $word,
            'guessed_letters' => implode(', ', $guessedLetters),
            'result' => $result,
            'timestamp' => now(),
        ];

        File::append(storage_path('logs/hangman.log'), json_encode($logData) . PHP_EOL);
    }
}
