#!/bin/bash
git-watch \
--url=http://git.watch/github/ScalaWilliam/woop.us \
--push-execute='
ref="%ref%"
if [ "$ref" = "refs/heads/master" ]; then
cd /home/ws/woop.us && 
git pull origin $ref &&
cd out &&
git pull origin gh-pages &&
rm -rf ./* &&
cd .. &&
php generator.php &&
cd out &&
git add -A . &&
git commit -a -m "Generating commit %sha%" &&
git status &&
git push origin HEAD
echo "Updated $ref"
fi'
