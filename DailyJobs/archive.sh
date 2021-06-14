#!/bin/bash

# This file is part of Impfpasserfassung.

echo "Starting Archive"
cd /home/webservice_impf/Impfpasserfassung/ArchiveJob
python3 job.py $(date '+%Y-%m-%d')
echo "Archive complete"