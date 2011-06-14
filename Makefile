test: test-reports

test-reports:
	php index.php ninja_unit_test/reports modules/unit_test/reports/*.tst

test-ci-prepare:
	@# get default config
	@service monitor stop
	@cp test/configs/all-host_service-states/etc/* /opt/monitor/etc/
	@cp test/configs/all-host_service-states/var/status.sav /opt/monitor/var/
	@service monitor start
	@# make sure users are imported to db:
	@php index.php 'cli/insert_user_data'

test-coverage: test-ci-prepare
	@php test/all_coverage.php $$(pwd)

docs: Documentation

Documentation: ninja.doxy application/models/*.php application/helpers/*.php
	doxygen ninja.doxy

help:
	@echo
	@echo Available make targets:
	@echo -----------------------
	@$(MAKE) --print-data-base --question | sed -n -e '/^Makefile/d' -e 's/^\([A-Za-z0-9_-]*\):.*/\1/p'
	@echo

wipe:
	php index.php ninja_unit_test/wipe_tables

.PHONY: test help test-reports
