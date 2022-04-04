#!/usr/bin/env bash
#all of our run steps
#         - "./scripts/lando-check-ssh-keys.sh"
#        - "./scripts/lando-project-set.sh"
#        - "./scripts/lando-platform-sync.sh"
#        - "cd /app && composer install"

#found out where we are so we can include the other files
DIR="${BASH_SOURCE%/*}"
#if BASH_SOURCE didn't return what we want, try PWD
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
#We're going to need some colors
if [[ -z ${CENTRY+x} ]]; then
    #pull in our global vars
    . "${DIR}/globvars.sh"
fi

#return it back to case sensitive
shopt -u nocasematch
