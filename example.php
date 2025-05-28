<?php

require 'vendor/autoload.php';

// Example usage:
$llamaClient = new PandaRose\OllamaStream(
    'http://127.0.0.1:11434/api/generate',
    'llama3.3',
    120,
    function (string $word): void {
        echo $word . " ";
    }
);

$words = $llamaClient->prompt('Tell me a joke about computers.');

echo "\n\nComplete response: $words\n";
