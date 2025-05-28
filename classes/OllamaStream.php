<?php

namespace PandaRose;

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\HttpException;
use Amp\Http\Client\Request;

class OllamaStream
{
    private $httpClient;
    private $url;
    private $model;
    private $timeout;
    private $onWordCallback;

    public function __construct(string $url, string $model, int $timeout, callable $onWordCallback)
    {
        $this->httpClient = HttpClientBuilder::buildDefault();
        $this->url = $url;
        $this->model = $model;
        $this->timeout = $timeout;
        $this->onWordCallback = $onWordCallback;
    }

    /**
     * Sends a prompt to the Ollama API and returns the sum total of the words received.
     *
     * @param string $prompt The prompt to send to the Ollama API.
     * @param bool $stream Whether to stream the response or not.
     *
     * @return string The total of the words received.
     */
    public function prompt(string $prompt, bool $stream = true): string
    {
        $request = new Request($this->url, "POST");
        $request->setTransferTimeout($this->timeout);

        $bodyData = json_encode([
            'model' => $this->model,
            'prompt' => $prompt,
            'stream' => $stream
        ]);

        $request->setHeader('Content-Type', 'application/json');
        $request->setBody($bodyData);

        try {
            $response = $this->httpClient->request($request);
            $wordCount = 0;
            $allWords = [];

            while (null !== $chunk = $response->getBody()->read()) {
                $jsonData = json_decode($chunk, true);
                if (isset($jsonData['response'])) {
                    $words = explode(' ', $jsonData['response']);
                    foreach ($words as $word) {
                        $word = trim($word, " ,.?!");
                        $allWords[] = $word;
                        if (!empty($word)) {
                            ($this->onWordCallback)($word);
                            $wordCount++;
                        }
                    }
                }
            }

            return implode(' ', $allWords);
        } catch (HttpException $e) {
            throw new RuntimeException("Failed to send request: " . $e->getMessage());
        }
    }
}
