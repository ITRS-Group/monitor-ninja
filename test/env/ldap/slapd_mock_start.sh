#!/bin/sh

pushd "$(dirname "$0")"
$(which slapd) -f slapd.conf -h ldap://0.0.0.0:13389
sleep 0.5
popd
