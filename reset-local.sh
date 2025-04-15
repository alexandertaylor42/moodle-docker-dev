#!/bin/bash

# -----------------------------------------------------------------------------
# reset-local.sh
# A script to completely reset the local Git working directory.
# It will:
#   1. Discard all uncommitted changes and staged files
#   2. Delete all untracked files and directories
#   3. Force sync the local working directory with origin/main
#
# WARNING: This will delete ALL local changes that are not committed.
# -----------------------------------------------------------------------------

set -e

echo "This script will DELETE all local uncommitted changes."
read -p "Are you sure you want to proceed? (y/N): " confirm

if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
    echo "Aborting reset."
    exit 1
fi

# Ensure we are operating on the latest remote state
echo "Fetching latest data from origin..."
git fetch origin

# Hard reset the working tree to match origin/main
echo "Resetting tracked files to match origin/main..."
git reset --hard origin/main

# Remove all untracked files and directories
echo "Removing untracked files and directories..."
git clean -fd

# Pull the clean latest copy
echo "Pulling fresh copy from origin/main..."
git pull origin main

echo "Reset complete. Local working directory now matches origin/main."

