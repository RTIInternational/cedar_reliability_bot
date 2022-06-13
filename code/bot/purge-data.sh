#!/bin/bash
echo Purging data previously used to populate CEDAR.
rm ./data/populateData-*.csv
rm ./data/metadataRecord-*.txt
echo done
