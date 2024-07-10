#!/usr/bin/env python3

import os
import sys
import subprocess
import json

# ANSI color codes
COR_VERDE = "\033[0;32m"
COR_AMARELA = "\033[0;33m"
COR_VERMELHA = "\033[0;31m"
COR_RESET = "\033[0m"

import os
import sys
import json
import subprocess
import requests

# Variable name for the OpenAI API key
env_var_name = 'OPENAI_API_KEY'

# Function to get the API key
def get_api_key():
    api_key = os.getenv(env_var_name)
    if api_key is None:
        print(f"{COR_VERMELHA}The OpenAI API key is not set.{COR_RESET}\nYou can create an API key at https://platform.openai.com/api-keys.\nPlease enter the API key: ")
        api_key = input().strip()
        if api_key:
            shell_name = os.path.basename(os.getenv('SHELL'))
            shell_config_file = os.path.join(os.getenv('HOME'), f'.{shell_name}rc')

            if shell_name == 'bash':
                shell_config_file = os.path.join(os.getenv('HOME'), '.bashrc')
            elif shell_name == 'zsh':
                shell_config_file = os.path.join(os.getenv('HOME'), '.zshrc')
            else:
                print(f"{COR_VERMELHA}It was not possible to determine the current shell configuration file.{COR_RESET}")
                sys.exit(1)

            with open(shell_config_file, 'a') as file:
                file.write(f'\nexport {env_var_name}="{api_key}"\n')

            print(f"{COR_VERDE}The API Key was added to your {shell_config_file}. {COR_AMARELA}Please restart the terminal or run `{COR_RESET}source {shell_config_file}{COR_AMARELA}` to apply the changes.{COR_RESET}")
            sys.exit(0)
        else:
            print(f"{COR_VERMELHA}Invalid API Key. Can't perform the request.{COR_RESET}")
            sys.exit(1)
    return api_key

# Function to interact with OpenAI API
def interact_with_openai(api_key, prompt, is_commit=False):
    headers = {
        'Authorization': f'Bearer {api_key}',
        'Content-Type': 'application/json'
    }
    data = {
        'model': 'gpt-4-turbo',
        'messages': [
            {
                'role': 'system',
                'content': 'You are an assistant that generates output for a Linux terminal. Please ensure all responses are formatted correctly and consider the limitations and capabilities of this environment when providing your response.'
            },
            {
                'role': 'user',
                'content': prompt
            }
        ],
        'temperature': 0.7,
        'max_tokens': 600 if not is_commit else 400
    }
    print(f"{COR_VERDE}Wait...{COR_RESET}")

    response = requests.post('https://api.openai.com/v1/chat/completions', headers=headers, data=json.dumps(data))
    response_data = response.json()

    if 'error' in response_data:
        print(f"{COR_VERMELHA}Error: {response_data['error']['message']}{COR_RESET}")
        sys.exit(1)

    for choice in response_data['choices']:
        response_text = choice['message']['content'].replace('```', '')
        print(f"{COR_AMARELA}=== Answer:{COR_RESET}")
        print(response_text)

        if is_commit:
            confirm = input(f"{COR_AMARELA}Do you want to add, commit, and push using this message? (Y/n): {COR_RESET}").strip().lower()
            if confirm == 'y' or confirm == '':
                subprocess.run("git add .", shell=True)
                commit_message = response_text.replace('\n', '\\n').replace('"', '\\"')
                subprocess.run(f'git commit -m "{commit_message}"', shell=True)
                subprocess.run("git push", shell=True)
        sys.exit(0)

# Main script execution
if __name__ == "__main__":
    api_key = get_api_key()
    is_commit = False

    if len(sys.argv) > 1:
        prompt = sys.argv[1]
        if prompt == 'commit':
            is_commit = True
            diff = subprocess.getoutput('git diff')
            if not diff:
                print(f"{COR_VERMELHA}No changes detected in git diff.{COR_RESET}\nPlease make changes before committing.")
                sys.exit(0)
            prompt = f"Write a concise semantic git commit message. Follow this format:\n1. The first line: A summary of the key changes, up to 60 characters, using conventional commits format.\n2. The second line: A blank line.\n3. The third line onwards: A detailed but concise description of the changes.\n\nUse the following git diff output as a reference for the commit message:\n: {diff}"
    else:
        print(f"{COR_VERDE}Hi. How can I help you?{COR_RESET}")
        prompt = input().strip()

    interact_with_openai(api_key, prompt, is_commit)

