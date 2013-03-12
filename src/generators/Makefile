MODULES=$(patsubst %/run_mod.php,%,$(wildcard */run_mod.php))

all: $(MODULES)

$(MODULES): force
	$(MAKE) -C $@

regenerate: clean
	$(MAKE) all

clean:
	rm $(MODULES:%=%/build_track)

.PHONY: force clean regenerate all
