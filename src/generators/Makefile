MODULES=$(patsubst %/run_mod.php,%,$(wildcard */run_mod.php))

all: $(MODULES)

$(MODULES): force
	$(MAKE) -C $@

regenerate:
	rm $(MODULES:%=%/build_track)
	$(MAKE) all

.PHONY: force regenerate all
