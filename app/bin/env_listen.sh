#!/bin/bash
CURRENT_DIR=$(dirname $(readlink -f "$0"))

WORK_DIR=$(dirname "$CURRENT_DIR")

FILE_NAME=$WORK_DIR'/.env'
LAST_ENV_TIME=`stat -c %Y  $FILE_NAME`
while true
do
    sleep 1
    #获取.env 的修改时间
    LAST_MODIFY_TIMESTAMP=`stat -c %Y  $FILE_NAME`
    if [ $LAST_MODIFY_TIMESTAMP -gt $LAST_ENV_TIME ]; then
        LAST_ENV_TIME=$LAST_MODIFY_TIMESTAMP
        date "+%Y-%m-%d %H:%M:%S"
        for process in `supervisorctl status |awk '{print $1}'`
          do
            if [ $process = 'envListener' ]; then
              continue
            fi
            #尝试重启
            supervisorctl restart $process
          done
    fi

done
