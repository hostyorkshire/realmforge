# DEPLOYMENT Instructions for Automating Code Updates

## Overview
This document outlines the steps required to automate code updates from the private repository using a deploy key.

## Prerequisites
1. Access to the private repository.
2. A server or CI/CD pipeline for deployment.

## Steps to Set Up Deploy Key
1. **Generate an SSH Key**:
   - Run the following command on your server/CI machine:
     ```bash
     ssh-keygen -t rsa -b 4096 -C "your_email@example.com"
     ```
   - Save the key to a secure location and do not use a passphrase.

2. **Add the Public Key to Your Repository**:
   - Go to your repository on GitHub.
   - Navigate to **Settings** > **Deploy keys**.
   - Click **Add deploy key** and provide the title and paste the public key.
   - Ensure the option for **Allow write access** is checked if write access is required.

3. **Store the Private Key Securely**:
   - Ensure that the private key (`id_rsa`) is stored securely on your server and access is restricted.
   
## Automating Deployment
- Use the following script as a guide to automate the code updates using the deploy key:

  ```bash
  #!/bin/bash
  # Change to the repository directory (cloned into the home directory)
  cd /home/playrealm

  # Add the deploy key to SSH agent
  eval `ssh-agent -s`
  ssh-add /path/to/your/private/key/id_rsa

  # Pull the latest code from the repository
  git pull origin main
  ```

## Security Notes
- **Access Control**: Grant deploy key access only to trusted servers and users. Review and rotate keys regularly.
- **Monitoring**: Keep track of who has access to the deploy keys and any associated scripts to prevent unauthorized access.
- **Limit Exposure**: If possible, restrict the deploy key's access to specific branches rather than the entire repository.

## Conclusion
Following the above steps will help you set up and automate code deployment securely using a deploy key. Ensure to follow security best practices to safeguard your code and deployment processes.