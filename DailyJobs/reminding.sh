#!/bin/bash

# This file is part of Impfpasserfassung.

echo "Starting Reminding"
cd /home/webservice_impf/Impfpasserfassung/AppointmentReminderJob
python3 job.py $(date '+%Y-%m-%d')
echo "Reminding complete"