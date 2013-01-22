SOURCES=$(wildcard *.php) $(wildcard ../lalr_parser/*.php) lsfilter.txt lscolumns.txt

build_track: $(SOURCES)
	php run_mod.php
	touch $@
