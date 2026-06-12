#!/bin/bash
set -e
cd /home/ipigb/laravelProjects/volamani
rm -f tools/inspect.sh tools/inspect2.sh tools/audit.sh tools/logcheck.sh tools/gitinit.sh.done
git init -b main
git add -A
git -c user.name="Volamani" -c user.email="ipigbeitsenegbemhe@gmail.com" commit -m "Initial commit: Volamani platform (Modules 0-21), test suite, CI, security hardening" --quiet
git log --oneline | head -2
git status -s | head -5
echo "tracked files: $(git ls-files | wc -l)"
