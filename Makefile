test: test-reports

test-reports:
	php index.php ninja_unit_test/reports modules/unit_test/reports/*.tst

docs: Documentation

Documentation: ninja.doxy application/models/*.php application/helpers/*.php
	doxygen ninja.doxy

help:
	@echo
	@echo Available make targets:
	@echo -----------------------
	@$(MAKE) --print-data-base --question | sed -n -e '/^Makefile/d' -e 's/^\([A-Za-z0-9_-]*\):.*/\1/p'
	@echo

.PHONY: test help test-reports
