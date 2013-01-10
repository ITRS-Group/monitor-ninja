SOURCES=$(wildcard *.php) $(wildcard ../lalr_parser/*.php) grammar.txt

build_track: $(SOURCES)
	php run_mod.php
	touch $@
