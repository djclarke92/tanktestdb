#!/bin/bash
#
#-----------------------------------------------------------------------
#
# TankTestDB release package script for raspberry pi
#
#-----------------------------------------------------------------------
#
# script to roll up a new release of TankTestDB

SRCFILE=./tanktestdb-$1.tgz
DEST=0
PKG=Y

if [[ $1 == "-?" || $1 == "" ]] ; then
	echo "Usage: $0 <release_version> [localhost|dest_hostname]"
	exit;
fi 

if [[ $2 != "" ]]; then
	DEST=$2
fi

echo $1> version.txt

if [[ $PKG = "Y" ]]; then
	echo "-> Create tar package file"
	tar -cvz --exclude=makerelease.sh --exclude=site_config.php --exclude=site_config.php.save --exclude="*~" --exclude=no.upgrade --exclude=no.tunnel \
		--exclude=releases --exclude=archive --exclude="*.cpp" --exclude="*.h" --exclude="*.o" --exclude="*.d" \
		--exclude="*.c" --exclude="Makefile*" --exclude="readconfig.txt" --exclude="*.svn*" --exclude="*.tgz" --exclude=tmp --exclude=websocketpp \
		--exclude=".*" --exclude="bin/obj" --exclude="bin/objarm" --file=${SRCFILE} *
fi


if [[ ! -f $SRCFILE && $PKG = "Y" ]]; then
	echo "-> Error: $SRCFILE is missing"
	exit 1
fi

echo ""
if [[ "$DEST" != 0 ]]; then
	for CC in $DEST; do
		PI=djclarke@$CC
		echo "-> Copying new version to ${PI}"
		if [ $CC = "localhost" -a -d /var/www/html/tanktestdb ]; then
			tar xvf ${SRCFILE}  -C /var/www/html/tanktestdb
			if [ $? != 0 ]; then
				echo "-> Error: failed to copy new package to /var/www/html/tanktestdb"
			fi
		else
			scp ${SRCFILE} ${PI}:/var/www/tanktestdb.nz 2>/dev/null
			if [ $? != 0 ]; then
				scp ${SRCFILE} ${PI}:~/.
				if [ $? != 0 ]; then
					echo "-> Error: failed to copy new package to ${PI}"
				else
					ssh ${PI} "(cd /var/www/tanktestdb.nz; tar -xvzf ~/${SRCFILE} --overwrite)"
					if [ $? != 0 ]; then
						echo "-> Error untaring ${SRCFILE}"
					fi
				fi
			fi
		fi
	done
else
	echo "-> Pi hostname(s) not set, skipping scp"
fi


echo "";

echo "-> Done.";
