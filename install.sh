#!/usr/bin/bash

# Remove the old version
sudo rm /usr/local/bin/gpt

# install the new version
sudo chmod +x gpt.php
sudo cp gpt.php /usr/local/bin/gpt

# Check if the gpt file exists in the /usr/bin directory
if [ -f /usr/local/bin/gpt ]; then
    echo "GPT has been installed successfully"
    echo "You have to open a new terminal or update the current terminal to use the script"
else
    echo "GPT has not been installed"
fi