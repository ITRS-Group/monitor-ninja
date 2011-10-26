#!/bin/sh

#
# Simple wrapper for automated ninja tests
# To be run like:
# sh /opt/monitor/op5/ninja/test/ci/testsuite.sh /opt/monitor/op5/ninja or
# sh /opt/monitor/op5/ninja/test/ci/testsuite.sh /opt/monitor/op5/ninja /opt/monitor/op5/ninja/test/ci/limited_tests.txt
#

errors=0
ntests=0

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
	the_test=`echo $line|awk '{print $1}'`
	the_user=`echo $line|awk '{print $2}'`
	if [ "$the_test" == "" -o "$the_user" == "" ]
	then
		echo "Malformed test found. Correct syntax: <request> <user>. Skipping:"
		echo $line
		echo
		return
	fi
	/usr/bin/php $prefix/index.php $the_test $the_user
	if [ $? -ne 0 ]
	then
		errors=$(($errors + 1))
	fi
	ntests=$(($ntests+1))
}

# Set loop separator to end of line
BAKIFS=$IFS
IFS=$(echo -en "\n\b")
exec 3<&0
exec 0<"$file"
while read -r line
do
	# use $line variable to process line in runTest() function
	if [ "$line" != "" ] && [ ${line:0:1} != "#" ]
	then
		runTest $line
	fi
done
exec 0<&3

# restore $IFS which was used to determine what the field separators are
IFS=$BAKIFS

echo "Executed $ntests tests"

if [ $errors -eq 0 ]
then
	echo "OK"
	exit 0
else
	echo "FAIL ($errors)"
	exit 1
fi
echo
