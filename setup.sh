#!/bin/bash

echo " Cloning Moodle core..."
git clone https://github.com/moodle/moodle.git
cd moodle
git checkout MOODLE_401_STABLE
cd ..

echo " Creating data directories..."
mkdir -p moodledata db

echo " Starting Docker services..."
docker compose up -d

echo " Done! Visit http://localhost:8080 to finish Moodle installation in your browser."

