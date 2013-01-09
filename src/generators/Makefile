MODULES=$(patsubst %/run_mod.php,%,$(wildcard */run_mod.php))

all:
	for MOD in $(MODULES); do cd $$MOD; $(MAKE); cd ..; done

clean:
	for MOD in $(MODULES); do cd $$MOD; $(MAKE) clean; cd ..; done
