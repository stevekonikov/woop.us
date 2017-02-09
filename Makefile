 
.PHONY: push
push:
	git pull origin HEAD
	cd out && git pull origin gh-pages && rm -rf ./* && cd ..
	php generator.php
	cd out && git add -A . && git commit -a -m "Generating commit $$(cd .. && git rev-parse HEAD)" && git push origin HEAD
	echo DONE
