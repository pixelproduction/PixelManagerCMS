#!/bin/bash

scriptname=`basename $0`
dbuser=""
dbname=""
dbpass=""
dbhost=""
sshuser=""
sshpass=""
sshserver=""
sshfolder=""

runsshcommand() {
    sshpass -p $sshpass ssh -oStrictHostKeyChecking=no $sshuser@$sshserver "$1"
}

checkSettings() {
    error=0

    if [ "$dbuser" == "" ]
      then
        echo "no db user given"
        error=1
    fi

    if [ "$dbname" == "" ]
      then
        echo "no db name given"
        error=1
    fi

    if [ "$dbpass" == "" ]
      then
        echo "no db pass given"
        error=1
    fi

    if [ "$dbhost" == "" ]
      then
        echo "no db host given"
        error=1
    fi

    if [ "$sshuser" == "" ]
      then
        echo "no ssh user given"
        error=1
    fi

    if [ "$sshpass" == "" ]
      then
        echo "no ssh pass given"
        error=1
    fi

    if [ "$sshserver" == "" ]
      then
        echo "no ssh server given"
        error=1
    fi

    if [ "$sshfolder" == "" ]
      then
        echo "no ssh folder given"
        error=1
    fi

    if [ "$error" == 1 ]
      then
        exit;
    fi
}

syncDb() {
    dbdump=/vagrant/$dbname.sql

    # dump database
    runsshcommand "mysqldump -h $dbhost -u $dbuser -p$dbpass --add-drop-table --databases $dbname" > $dbdump

    if [ ! -f "$dbdump" ]
      then
        echo "Could not create database dump"
        exit
    fi

    # import database
    mysql -u $dbuser -p$dbpass $dbname < $dbdump

    rm $dbdump
}

syncFolder() {
    # download from remote
    runsshcommand "cd $sshfolder; tar -czf $1.tar.gz $1"
    sshpass -p $sshpass scp -r $sshuser@$sshserver:${sshfolder%/}/$1.tar.gz /vagrant/
    runsshcommand "cd $sshfolder; rm -f $1.tar.gz"

    tarfile="/vagrant/$1.tar.gz"

    if [ ! -f "$tarfile" ]
      then
        echo "Could not download user data"
        exit
    fi

    if [ -d "/vagrant/$1" ]
      then
        # delete local copy
        rm -Rf "/vagrant/$1"
    fi

    # extract downloaded tar ball
    tar -xzf $tarfile -C /vagrant

    # delete tarball
    rm -f "$tarfile"
}

cleanup() {
    rm -Rf /vagrant/.ssh
}

# parse arguments
while [ "$1" != "" ]; do
  opt=$1
  shift
  val=$1
  shift

  case $opt in
    -dbuser)
      dbuser=$val
      ;;
    -dbname)
      dbname=$val
      ;;
    -dbhost)
      dbhost=$val
      ;;
    -dbpass)
      dbpass=$val
      ;;
    -sshpass)
      sshpass=$val
      ;;
    -sshuser)
      sshuser=$val
      ;;
    -sshserver)
      sshserver=$val
      ;;
    -sshfolder)
      sshfolder=$val
      ;;
    *)
      echo "$scriptname: invalid option -- $opt"
      exit
      ;;
  esac
done

checkSettings
syncDb
syncFolder "user-data"
cleanup

echo "ok"