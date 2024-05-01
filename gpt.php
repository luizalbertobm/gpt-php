#!/usr/bin/php

<?php
// To install it globally, download the raw file and run the following commands:
// sudo cp gpt.php /usr/local/bin/gpt
// sudo chmod +x /usr/local/bin/gpt

$cor_verde = "\033[0;32m";
$cor_amarela = "\033[0;33m";
$cor_vermelha = "\033[0;31m";
$cor_reset = "\033[0m";

// Variable name for the OpenAI API key
$envVarName = 'OPENAI_API_KEY';

// Check if the OpenAI API key is set as an environment variable
if (getenv($envVarName) === false) {
    echo $cor_vermelha . "A chave da API da OpenAI não está definida.\nVocê pode criar uma chave em https://platform.openai.com/api-keys.\nPor favor, insira a chave da API: ". $cor_reset . PHP_EOL;
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
                // Adicione mais casos conforme necessário para outros shells
            default:
                echo $cor_vermelha . "Não foi possível determinar o arquivo de configuração do shell atual." . $cor_reset . PHP_EOL;
                exit;
        }

        // Add the API key to the shell configuration file
        file_put_contents($shellConfigFile, "\nexport $envVarName=\"$apiKey\"\n", FILE_APPEND);

        // Inform the user that the API key has been added and provide instructions to reload the configuration file
        echo $cor_vermelha . "A chave da API foi adicionada ao seu $shellConfigFile. Por favor, reinicie o terminal ou execute o comando 'source $shellConfigFile' para aplicar as mudanças." . $cor_reset . PHP_EOL;
        exit;
    } else {
        echo $cor_vermelha . "Chave da API inválida. Não é possível executar o comando." . $cor_reset . PHP_EOL;
        ;
        exit;
    }
}

$isCommit = false;
// Check if the script was called with an argument
if($argc > 1) {
    echo $cor_verde . "Gerando mensagem..." . $cor_reset . PHP_EOL;
    $prompt = $argv[1];
    // If the parameter is 'commit', the script will generate a commit message based on the git diff
    if($prompt == 'commit') {
        $isCommit = true;
        $diff = shell_exec('git diff');
        $prompt = "Crie uma mensagem de commit seguindo os padrões de commits semânticos. Baseie-se na seguinte saída do 'git diff': $diff.\n A mensagem deve ser direta e clara, focada apenas nas alterações relevantes.";
    }
} else {
    echo $cor_verde . "Olá. Como posso ajudá-lo hoje?". $cor_reset . PHP_EOL;
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
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Você é um assistente que irá gerar uma saída em um terminal Linux. Por favor, leve em consideração todas as limitações e recursos deste ambiente ao fornecer sua resposta.',
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
echo $cor_amarela . "=== Resposta:" . $cor_reset . PHP_EOL;
if(isset($responseData['error'])) {
    echo "Erro: " . $responseData['error']['message'] . "\n";
    exit(1);
}

foreach ($responseData['choices'] as $choice) {
    $response = $choice['message']['content'];
    echo $response  . "\n";

    if($isCommit) {
        echo "Deseja adicionar, commitar e fazer push com esta mensagem? (yes/no): ";
        $confirmacao = trim(fgets(STDIN));  // Lê a entrada do usuário

        // Confirm the user's intention to add, commit, and push the response
        if (strtolower($confirmacao) === 'yes') {
            shell_exec("git add .");
            shell_exec("git commit -m \"$response\"");
            shell_exec("git push");
        }
    }
    exit(0);
}
