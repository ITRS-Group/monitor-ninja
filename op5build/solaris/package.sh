#!/bin/bash

eval `cat pkginfo | grep NAME`
product=$NAME
echo $product
version=dafault
user=default
tmpdir=/tmp/build

while getopts "hv:u:" OPTION; do
  case $OPTION in
	h)
	echo "-u <gituser> -v <version>"
	exit 1
	;;
	v)
	version=$OPTARG
	;;
       u)
        user=$OPTARG
        ;;
	*)
	echo "BadArgs"
	exit 1
 esac
done
if [ $version == "default" ] || [ $user == "default" ]; then
	echo "Invalid arguments. try -h"
	exit 1
fi

git clone $user@devel.int.op5.se:~git/monitor/$product.git
working_dir=`pwd`
mkdir temp

####
if [ -f build ]; then
	/bin/bash build;
fi
####

pushd temp
	if [ -f ../checkinstall ] ; then echo i checkinstall >> ../prototype; fi
        if [ -f ../preinstall ] ; then echo i preinstall >> ../prototype; fi
        if [ -f ../postinstall ] ; then echo i postinstall >> ../prototype; fi

	echo 'i pkginfo' >> ../prototype
	pkgproto * >> ../prototype
	#sed -e "s|nagios nagios|root root|" ../prototype >> prototype

        mv ../prototype ./
        if [ -f ../checkinstall ] ; then cp ../checkinstall ./; fi
        if [ -f ../preinstall ] ; then cp ../preinstall ./; fi
        if [ -f ../postinstall ] ; then cp ../postinstall ./; fi

        cp ../pkginfo pkginfo
        
	sed -e "s/@@VERSION@@/$version/g" pkginfo > pkginfo1 && mv pkginfo1 pkginfo
        
	#pkgproto $(PACKDIR)=/ | sed -e "s|$(LOGNAME) $(GROUP)$$|root root|" | egrep -v "(s|d) none (/|/etc|/var|/usr|/usr/local) " >> Prototype        
        mkdir -p $tmpdir/$product
        pkgmk -o -r ./ -d $tmpdir/$product -f prototype
        eval `cat pkginfo | grep PKG`
	eval `cat pkginfo | grep ARCH`
popd


pkgtrans -s $tmpdir/$product `pwd`/$PKG-$version-$ARCH.pkg $PKG
echo "Package ready: $PKG-$version-$ARCH.pkg"

echo "Performing cleanup..."
#rm -rf $working_dir/temp
rm -rf $tmpdir
echo "Done"
