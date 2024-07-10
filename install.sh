#!/usr/bin/bash

install_php_version() {
    # Remove the old version
    sudo rm /usr/local/bin/gpt

    # Install the new PHP version
    sudo chmod +x gpt.php
    sudo cp gpt.php /usr/local/bin/gpt

    # Check if the gpt file exists in the /usr/bin directory
    if [ -f /usr/local/bin/gpt ]; then
        echo "PHP version of GPT has been installed successfully"
        echo "You have to open a new terminal or update the current terminal to use the script"
    else
        echo "PHP version of GPT has not been installed"
    fi
}

install_python_version() {
    # Remove the old version
    sudo rm /usr/local/bin/gpt

    # Install the new Python version
    sudo chmod +x gpt.py
    sudo cp gpt.py /usr/local/bin/gpt

    # Check if the gpt file exists in the /usr/bin directory
    if [ -f /usr/local/bin/gpt ]; then
        echo "Python version of GPT has been installed successfully"
        echo "You have to open a new terminal or update the current terminal to use the script"
    else
        echo "Python version of GPT has not been installed"
    fi
}

echo "Choose which version of GPT to install:"
echo "1) PHP version"
echo "2) Python version"
read -p "Enter the number corresponding to your choice: " choice

case $choice in
    1)
        install_php_version
        ;;
    2)
        install_python_version
        ;;
    *)
        echo "Invalid choice. Please run the script again and choose 1 or 2."
        ;;
esac