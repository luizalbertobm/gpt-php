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

# Environment variable name for the OpenAI API key
ENV_VAR_NAME = 'OPENAI_API_KEY'

def prompt_user(message):
    """Prompt the user for input."""
    return input(message)

def add_api_key_to_shell_config(api_key):
    """Add the API key to the appropriate shell configuration file."""
    shell_name = os.path.basename(os.getenv('SHELL'))
    shell_config_file = os.path.expanduser(f'~/.{shell_name}rc')

    if shell_name == 'bash':
        shell_config_file = os.path.expanduser('~/.bashrc')
    elif shell_name == 'zsh':
        shell_config_file = os.path.expanduser('~/.zshrc')
    else:
        print(f"{COR_VERMELHA}It was not possible to determine the current shell configuration file.{COR_RESET}")
        sys.exit(1)

    with open(shell_config_file, 'a') as file:
        file.write(f'\nexport {ENV_VAR_NAME}="{api_key}"\n')

    print(f"{COR_VERMELHA}The API Key was added to your {shell_config_file}. Please restart the terminal or run 'source {shell_config_file}' to apply the changes.{COR_RESET}")

def get_api_key():
    """Get the OpenAI API key from the environment variable or prompt the user for it."""
    api_key = os.getenv(ENV_VAR_NAME)
    if not api_key:
        api_key = prompt_user(f"{COR_VERMELHA}The OpenAI API key is not set.\nYou can create an API key at https://platform.openai.com/api-keys.\nPlease enter the API key: {COR_RESET}")
        if api_key:
            add_api_key_to_shell_config(api_key)
            sys.exit()
        else:
            print(f"{COR_VERMELHA}Invalid API Key. Can't perform the request.{COR_RESET}")
            sys.exit(1)
    return api_key

def generate_prompt(is_commit):
    """Generate the prompt based on the script arguments."""
    if len(sys.argv) > 1:
        prompt = sys.argv[1]
        if prompt == 'commit':
            is_commit = True
            diff = subprocess.getoutput('git diff')
            prompt = (
                "Write a concise semantic git commit message.\n"
                "- The first line must start with a 60-character summary of the key changes.\n"
                "- The second line must be a blank line\n"
                "- The third line onwards should provide a longer but concise description of the changes\n"
                "The `git diff` output for the commit message is the following:\n" + diff
            )
    else:
        print(f"{COR_VERDE}Hi. How can I help you?{COR_RESET}")
        prompt = input()
    return prompt, is_commit

def get_openai_response(api_key, prompt, is_commit):
    """Get the response from the OpenAI API."""
    headers = {
        'Authorization': f'Bearer {api_key}',
        'Content-Type': 'application/json'
    }

    data = {
        'model': 'gpt-4-turbo',
        'messages': [
            {
                'role': 'system',
                'content': 'You are an assistant generating output for a Linux terminal. Please consider all the limitations and capabilities of this environment when providing your response.',
            },
            {
                'role': 'user',
                'content': prompt,
            }
        ],
        'temperature': 0.7,
        'max_tokens': 400 if is_commit else 600
    }

    print(f"{COR_VERDE}Wait...{COR_RESET}")

    response = subprocess.getoutput(f"curl -s -X POST https://api.openai.com/v1/chat/completions -H 'Authorization: Bearer {api_key}' -H 'Content-Type: application/json' -d '{json.dumps(data)}'")
    response_data = json.loads(response)

    if 'error' in response_data:
        print(f"Error: {response_data['error']['message']}")
        sys.exit(1)

    return response_data

def handle_commit(response):
    """Handle the commit process."""
    response = response.replace('```', '')
    print(response)
    confirmation = prompt_user("Do you want to add, commit, and push with this message? (Y/n): ")
    if confirmation.lower() in ['y', 'yes', '']:
        escaped_response = response.replace('"', '\\"')
        subprocess.call(f'git add .', shell=True)
        subprocess.call(f'git commit -m "{escaped_response}"', shell=True)
        subprocess.call(f'git push', shell=True)

def main():
    api_key = get_api_key()
    prompt, is_commit = generate_prompt(is_commit=False)
    response_data = get_openai_response(api_key, prompt, is_commit)

    print(f"{COR_AMARELA}=== Answer:{COR_RESET}")
    for choice in response_data['choices']:
        response = choice['message']['content']
        print(response)
        if is_commit:
            handle_commit(response)

if __name__ == '__main__':
    main()
