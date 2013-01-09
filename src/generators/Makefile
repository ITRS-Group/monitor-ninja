MODULES=$(patsubst %/run_mod.php,%,$(wildcard */run_mod.php))

all: $(MODULES)

.PHONY: force

$(MODULES): force
	$(MAKE) -C $@
