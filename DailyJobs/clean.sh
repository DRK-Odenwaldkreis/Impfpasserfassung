#!/bin/bash

# This file is part of Impfpasserfassung.

echo "Starting Cleaning"
rm -rf /home/webservice_impf/LoginSheet/*
rm -rf /home/webservice_impf/Tickets/*
rm -rf /home/webservice_impf/Zertifikate/*
echo "Cleaning complete"