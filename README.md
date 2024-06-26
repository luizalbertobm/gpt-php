# OpenAI Console Chat

Este repositório contém um script PHP para integrar o terminal com a API da OpenAI. O script permite que você faça perguntas e receba respostas geradas pelo modelo de linguagem da OpenAI, além de gerar mensagens de commits semânticas.

## Exemplos

- Pergunta simples:

![imagem exemplo](example.png)

- Geração de commits semânticos para git:

![imagem exemplo](commit.png)

## Pré-requisitos

Antes de executar o script, você precisa ter:
- PHP 7.4 ou superior
- A extensão do curl para o php

Você pode instalar as duas usando o comando a seguir:
```bash
sudo apt instal php php php-curl
```

## Configuração
Para executar o script, você também precisa de uma chave de API da OpenAI. Se você ainda não tem uma chave de API, pode criá-la em [https://platform.openai.com/api-keys](https://platform.openai.com/api-keys).

O script verifica se a chave da API da OpenAI está definida como uma variável de ambiente. Se não estiver definida, solicitará que você insira a chave da API e a adicionará ao arquivo de configuração do shell para persistência.

O script é compatível com os shells `bash` e `zsh`. Ele determinará automaticamente o arquivo de configuração correto com base no shell atual do usuário.

## Como instalar
Crie o link simbólico para o script no diretório `/usr/local/bin`:
```bash
sudo ln -s "$(pwd)/gpt.php" /usr/local/bin/gpt
```

Em seguida, torne o script executável e atualize o shell para reconhecer o novo comando:
```bash
sudo chmod +x gpt.php
source ~/.bashrc
```

Agora você pode executar o script globalmente no terminal digitando `gpt` e passando os argumentos necessários.
```bash
#Exemplo:
gpt "qual a capital do Brasil?"
```

## Limitações

Este script é uma implementação básica para interagir com a API da OpenAI e pode ter algumas limitações. Certifique-se de revisar a documentação da OpenAI para obter informações detalhadas sobre como usar a API e quaisquer limitações ou restrições aplicáveis.

## Contribuições

Se você encontrar problemas ou tiver sugestões de melhoria, sinta-se à vontade para abrir uma issue ou enviar uma solicitação de pull request.

## Licença

Este projeto é livre para cópia e distribuição sob a licença [MIT](LICENSE)
