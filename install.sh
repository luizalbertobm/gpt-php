#!/usr/bin/bash

# remove the old gpt file
sudo rm /usr/local/bin/gpt

# Copy the gpt.php file to the /usr/bin directory renaming it to only gpt
sudo cp gpt.php /usr/local/bin/gpt
sudo chmod +x /usr/local/bin/gpt

echo "GPT has been installed successfully"
echo "You have to open a new terminal or update the current terminal to use the script"