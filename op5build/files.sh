#!/bin/sh


while read line; do
  if [ -f "$line" ]; then
    dirname=`dirname "$line"`
    mkepmlist --prefix /opt/monitor/op5/ninja/$dirname -g root -u root $line
  fi
  if [ -d "$line" ]; then
   mkepmlist --prefix /opt/monitor/op5/ninja/$line -g root -u root  $line
 fi
done < op5build/files.in





