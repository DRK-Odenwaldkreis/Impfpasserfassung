#!/bin/bash

# This file is part of Impfpasserfassung.

echo "Starting Cleaning"
cd /home/webservice_impf/Impfpasserfassung/NightlyAutoClean && python3 job.py >> ../../Logs/cleanJob.log 2>&1
python3 job.py
echo "Cleaning complete"