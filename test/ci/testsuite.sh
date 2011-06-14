#!/bin/sh

#
# Simple wrapper for automated ninja tests
# To be run like:
# sh /opt/monitor/op5/ninja/test/ci/testsuite.sh /opt/monitor/op5/ninja or
# sh /opt/monitor/op5/ninja/test/ci/testsuite.sh /opt/monitor/op5/ninja /opt/monitor/op5/ninja/test/ci/limited_tests.txt
#

errors=0

if [ $# -ge 1 ]
then
	prefix=$1
else
	prefix="/opt/monitor/op5/ninja";
fi

if [ $# -ge 2 ]
then
	file=$2
else
	file="$prefix/test/ci/ninjatests.txt"
fi

runTest()
{
	line="$@"
    if [ "$line" != "" ]
	then
		the_test=`echo $line|awk '{print $1}'`
		the_user=`echo $line|awk '{print $2}'`
		/usr/bin/php $prefix/index.php $the_test $the_user
   		if [ $? -ne 0 ]
    	then
        	errors=$(($errors + 1))
    	fi
	fi
}

# Set loop separator to end of line
BAKIFS=$IFS
IFS=$(echo -en "\n\b")
exec 3<&0
exec 0<"$file"
while read -r line
do
	# use $line variable to process line in runTest() function
	runTest $line
done
exec 0<&3

# restore $IFS which was used to determine what the field separators are
IFS=$BAKIFS

if [ $errors -eq 0 ]
then
	echo "OK"
	exit 0
else
	echo "FAIL"
	exit 1
fi
echo
