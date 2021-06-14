#!/bin/bash

# This file is part of Impfpasserfassung.

echo "Starting Report"
cd /home/webservice_impf/Impfpasserfassung/TagesReport
python3 job.py $(date '+%Y-%m-%d') 1
chown www-data:www-data /home/webservice_impf/Reports/Tagesreport_*
echo "Reporting complete"