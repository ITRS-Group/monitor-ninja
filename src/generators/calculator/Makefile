SOURCES=$(wildcard *.php) $(wildcard ../lalr_parser/*.php) grammar.yml

build_track: $(SOURCES)
	php run_mod.php
	touch $@
