#!/bin/bash

# Cron Job Mobile Inspection (2019-07-25 18:58)
# ----------------------------------------------------------
# Note:
# - Mengganti mode folder utama dari file TR_INSPECTION.csv,
#   TR_INSPECTION_IMG.csv, dan TR_INSPECTION_PATH.csv.

# Path Folder File TR_INSPECTION.csv, TR_INSPECTION_IMG.csv, dan TR_INSPECTION_PATH.csv
PATH="/mobile_inspection/";

# Run ganti mode folder
/bin/chmod -R 777 "$PATH";