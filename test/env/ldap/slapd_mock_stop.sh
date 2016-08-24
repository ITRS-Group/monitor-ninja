#!/bin/sh

set -e

if test -f /tmp/op5libtest_slapd.pid; then
	kill $(cat /tmp/op5libtest_slapd.pid)
fi
