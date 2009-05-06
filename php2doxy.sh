#!/bin/sh

if ! test "$@"; then
	echo "phpdoc to doxygen conversion"
	echo
	echo "Usage: $0 [-i] [-e sed-expression] file1 file2 file3 filen..."
	echo
	echo "  -i provides in-place editing of the files given as arguments"
	echo "  -e lets you add additional sed expressions to the command line"
	echo
	echo "If run without '-i', output is sent to stdout"
	exit 1
fi

sed -e '/^[\t ]*\*[\t ]*@name/d' \
	-e 's/^\([\t ]*\*\)[\t ]\+/\1 /' \
	-e 's/^\(\t*\)[\t ]*\*/\1 */' \
	-e 's/^\([\t ]*\*\)[\t ]*\(@[A-Za-z0-9_-]*\)[\t ]*/\1 \2 /' \
	-e 's/^\([\t ]*\*\)[\t ]*@desc[\t ]*\(.*\)/\1 \2/' \
	-e 's/\(* @param\) [^$ ]*\(.*\)/\1\2/' \
	-e 's/[\t ]*$//' \
	-e 's/^class[\t ]*\([^ {]*\)[\t ]*{/class \1\n{/' \
	"$@"
