#!/bin/bash

# Replace Notification Hook recipients with new url
echo "Updating Notifications..."
gsed -i 's/http:\/\/.*.ngrok.io/http:\/\/'$1'.ngrok.io/g' wbnotifications.xml

# Output
echo "Done."
cat wbnotifications.xml | grep "ngrok"