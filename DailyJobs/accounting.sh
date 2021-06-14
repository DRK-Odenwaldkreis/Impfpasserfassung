#!/bin/bash

# This file is part of Impfpasserfassung.

echo "Starting Accounting"
cd /home/webservice_impf/Impfpasserfassung/AccountJob
python3 job.py $(date '+%Y-%m-%d')
echo "Accounting complete"