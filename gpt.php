#!/usr/bin/php

<?php
// sudo wget -O /usr/local/bin/script.sh https://gist.githubusercontent.com/luizalbertobm/f9331f25211732752e77e7065b72acca/raw/6d6769d2d8f6a0a1f4b447c0f45b0aff6780568c/
// sudo chmod +x /usr/local/bin/*

$cor_verde = "\033[0;32m";
$cor_amarela = "\033[0;33m";
$cor_vermelha = "\033[0;31m";
$cor_reset = "\033[0m";

// Nome da variável de ambiente
$envVarName = 'OPENAI_API_KEY';

// Verifica se a variável de ambiente já está definida
if (getenv($envVarName) === false) {
    echo $cor_vermelha . "A chave da API da OpenAI não está definida.\nVocê pode criar uma chave em https://platform.openai.com/api-keys.\nPor favor, insira a chave da API: ". $cor_reset . PHP_EOL;
    $handle = fopen("php://stdin", "r");
    $apiKey = trim(fgets($handle));

    if (!empty($apiKey)) {
        // Determina o arquivo de configuração do shell com base no shell atual
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

        // Adiciona a variável de ambiente ao arquivo de configuração
        file_put_contents($shellConfigFile, "\nexport $envVarName=\"$apiKey\"\n", FILE_APPEND);

        // Informa ao usuário que a chave foi adicionada e instruções para recarregar o arquivo de configuração
        echo $cor_vermelha . "A chave da API foi adicionada ao seu $shellConfigFile. Por favor, reinicie o terminal ou execute o comando 'source $shellConfigFile' para aplicar as mudanças." . $cor_reset . PHP_EOL;
        exit;
    } else {
        echo $cor_vermelha . "Chave da API inválida. Não é possível executar o comando." . $cor_reset . PHP_EOL;
        ;
        exit;
    }
}

$isCommit = false;
// Check if the user has provided a command line parameter
if($argc > 1) {
    echo $cor_verde . "Gerando mensagem..." . $cor_reset . PHP_EOL;
    $prompt = $argv[1];
    if($prompt == 'commit') {
        $isCommit = true;
        $diff = shell_exec('git diff');
        $prompt = "Escreva apenas a mensagem de commit seguindo o padrao de commits semanticos (sem texto introdutório nem exemplo, diretamente a mensagem) para a seguinte saida do git diff: $diff";
    }
} else {
    // echo 'Por favor faça uma pergunta. Por exemplo `gpt "Qual a capital da Fraça"`';
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
    $data['max_tokens'] = 200;
    // unset($data['messages'][0]);
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
        shell_exec("git add .");
        shell_exec("git commit -m \"$response\"");
        shell_exec("git push");
    }    
    exit(0);
}
