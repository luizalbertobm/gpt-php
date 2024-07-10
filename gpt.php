#!/usr/bin/php

<?php
// To install it globally, follow these steps:
// - sudo cp gpt.php /usr/local/bin/gpt
// - sudo chmod +x /usr/local/bin/gpt
// After that, you can run the script from anywhere by typing `gpt` in a new fresh terminal.

$cor_verde = "\033[0;32m";
$cor_amarela = "\033[0;33m";
$cor_vermelha = "\033[0;31m";
$cor_reset = "\033[0m";

// Variable name for the OpenAI API key
$envVarName = 'OPENAI_API_KEY';

// Check if the OpenAI API key is set as an environment variable
if (getenv($envVarName) === false) {
    echo $cor_vermelha . "The OpenAI API key is not set.\nYou can create an API key at https://platform.openai.com/api-keys.\nPlease enter the API key: ". $cor_reset . PHP_EOL;
    $handle = fopen("php://stdin", "r");
    $apiKey = trim(fgets($handle));

    if (!empty($apiKey)) {
        // Determine the shell configuration file based on the current shell
        $shellName = basename($_SERVER['SHELL']);
        $shellConfigFile = $_SERVER['HOME'] . '/.'.$shellName.'rc';

        switch (basename($_SERVER['SHELL'])) {
            case 'bash':
                $shellConfigFile = $_SERVER['HOME'] . '/.bashrc';
                break;
            case 'zsh':
                $shellConfigFile = $_SERVER['HOME'] . '/.zshrc';
                break;
            default:
                echo $cor_vermelha . "It was not possible to determine the current shell configuration file." . $cor_reset . PHP_EOL;
                exit;
        }

        // Add the API key to the shell configuration file
        file_put_contents($shellConfigFile, "\nexport $envVarName=\"$apiKey\"\n", FILE_APPEND);

        // Inform the user that the API key has been added and provide instructions to reload the configuration file
        echo $cor_vermelha . "The API Key was added to your $shellConfigFile. Please restart the terminal or run `source $shellConfigFile` to apply the changes." . $cor_reset . PHP_EOL;
        exit;
    } else {
        echo $cor_vermelha . "Invalid API Key. Can't perform the request." . $cor_reset . PHP_EOL;
        ;
        exit;
    }
}

$isCommit = false;
// Check if the script was called with an argument
if($argc > 1) {
    $prompt = $argv[1];
    // If the parameter is 'commit', the script will generate a commit message based on the git diff
    if($prompt == 'commit') {
        $isCommit = true;
        $diff = shell_exec('git diff');
        $prompt = "Write a concise semantic git commit message. Follow this format:
            1. The first line: A summary of the key changes, up to 60 characters, using conventional commits format.
            2. The second line: A blank line.
            3. The third line onwards: A detailed but concise description of the changes.

            Use the following git diff output as a reference for the commit message:\n: $diff";
    }
} else {
    echo $cor_verde . "Hi. How can I help you?". $cor_reset . PHP_EOL;
    $prompt = readline();
}


// Your OpenAI API key
$apiKey = getenv($envVarName);

// Request Header
$headers = [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
];

// Data to be sent in the request
$data = [
    // 'model' => 'gpt-3.5-turbo',
    'model' => 'gpt-4-turbo',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are an assistant that generates output for a Linux terminal. Please ensure all responses are formatted correctly and consider the limitations and capabilities of this environment when providing your response.',
        ],
        [
            'role' => 'user',
            'content' => $prompt,
        ]
    ],
    'temperature' => 0.7,
    'max_tokens' => 600  // Adjust the number of tokens as needed
];
if($isCommit) {
    $data['max_tokens'] = 400;
}
echo $cor_verde . "Wait..." . $cor_reset . PHP_EOL;

// API endpoint for OpenAI (you may need to modify this based on the specific API you are using)
$apiUrl = 'https://api.openai.com/v1/chat/completions';

// Initialize cURL session
$ch = curl_init($apiUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the POST request
$response = curl_exec($ch);

// Close cURL session
curl_close($ch);

// Decode and display the response
$responseData = json_decode($response, true);
echo $cor_amarela . "=== Answer:" . $cor_reset . PHP_EOL;
if(isset($responseData['error'])) {
    echo "Erro: " . $responseData['error']['message'] . "\n";
    exit(1);
}

foreach ($responseData['choices'] as $choice) {
    $response = $choice['message']['content'];
    $response = str_replace('```', '', $response);

    echo $response . PHP_EOL;

    if($isCommit) {
        echo $cor_amarela . "Do you want to add, commit, and push with this message? (Y/n):" . $cor_reset;
        $confirmacao = trim(fgets(STDIN));  // Lê a entrada do usuário

        // Confirm the user's intention to add, commit, and push the response
        if (strtolower($confirmacao) === 'y' || empty($confirmacao)) {
            $escapedResponse = escapeshellarg($response);
            shell_exec("git add .");
            shell_exec("git commit -m $escapedResponse");
            shell_exec("git push");
        }
    }
    exit(0);
}
