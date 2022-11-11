#!/bin/bash
ID=`ps -ef | grep worker-s | grep -v grep | awk '{print $2}'`
echo $ID
for id in $ID
do
kill -9 $id
echo "kill $id"
done