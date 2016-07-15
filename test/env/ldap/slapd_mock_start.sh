#!/bin/sh

SLAPD=false
# Hack because SLES is wierd...
if test -x /usr/lib/openldap/slapd; then
	SLAPD=/usr/lib/openldap/slapd
else
	SLAPD=$(which slapd)
fi

pushd "$(dirname "$0")"
$SLAPD -f slapd.conf -h ldap://0.0.0.0:13389
sleep 0.5
popd
