# OpenAI Console Chat

Este repositório contém um único arquivo com um script PHP para interagir com a API da OpenAI e realizar conversas de texto. O script permite que você faça perguntas e receba respostas geradas pelo modelo de linguagem da OpenAI.

## Pré-requisitos

Antes de executar o script, você precisa ter o PHP 7.4 ou superior instalado em seu sistema. Você pode verificar se o PHP está instalado executando o seguinte comando no terminal:
```bash
php -v
```

Você também pode instalar o PHP no linux usando 
```bash
sudo apt instal php php php-curl
```

## Configuração
Para executar o script, você também precisa de uma chave de API da OpenAI. Se você ainda não tem uma chave de API, pode criá-la em [https://platform.openai.com/api-keys](https://platform.openai.com/api-keys).

O script verifica se a chave da API da OpenAI está definida como uma variável de ambiente. Se não estiver definida, solicitará que você insira a chave da API e a adicionará ao arquivo de configuração do shell para persistência.

O script é compatível com os shells `bash` e `zsh`. Ele determinará automaticamente o arquivo de configuração correto com base no shell atual do usuário.

## Como usar

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
