 
.PHONY: \
	build \
	test \
	clean \

build:
	mkdir -p out
	php generator.php

test:
	echo nothing to test

clean:
	rm -rf out
