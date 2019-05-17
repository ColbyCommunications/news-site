#!/usr/bin/env bash
# Tests the connection to vcs
#Has DIR already been set up?
if [[ -z ${DIR+x} ]]; then
    # No? ok, figure out where we are
    DIR="${BASH_SOURCE%/*}"
    if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
fi

#Has our colors file already been pulled in
if [[ -z ${CENTRY+x} ]]; then
    #pull in our global vars
    . "${DIR}/globvars.sh"
fi

printf "\n${CINFO}Checking connection to vcs.missouri.edu... ${CRESET}"
ssh -q -o BatchMode=yes -o ConnectTimeout=10 "${VCSSSHDOMAIN}" exit
VCSSUCCESS=$?
if (( 0 != $VCSSUCCESS )); then
    printf "${BOLD}Failure!${CRESET}\n"
    printf "${CINFO}I was unable to connect successfully to ${VCSSSHDOMAIN}. The composer build step if I am unable \n"
    printf "to connect. Please make sure you are on a campus network (wired, wireless, or on the VPN) and that you \n"
    printf "an ssh pub key from this machine to your account on ${VCSSSHDOMAIN}. If you are unsure, go to \n"
    printf "https://${VCSSSHDOMAIN}/profile/keys and check if you have a key listed that is from this machine. \n"
    
    if [[ ! -z ${PLATFORMSSHKEY+x} ]]; then
        pbcopy "${PLATFORMSSHKEY}.pub"
        printf "\nI have copied the contents of the ssh pub key we created in a previous step. If you don't have an ssh "
        printf "key on ${VCSSSHDOMAIN} you can paste the contents into the ${CBOLD}key${CRESET}${CINFO} area at \n"
        printf "https://${VCSSSHDOMAIN}/profile/keys and click ${CBOLD}Add key${CRESET}.\n"
    fi

    printf "\n${CINFO}Once you have verified you are on a campus network and have an ssh key associated with your account\n"
    printf " on ${VCSSSHDOMAIN}, press enter and I will test the connection again. : [Enter]"
    read VCSCONTINUE
    unset VCSSUCCESS
    ssh -q -o BatchMode=yes -o ConnectTimeout=10 "${VCSSSHDOMAIN}" exit
    VCSSUCCESS=$?

    if (( 0 != $VCSSUCCESS )); then
        #This should already be set, but just in case it inst
        if [[ -z ${PLATFORMPROJECTID} ]]; then
            PLATFORMPROJECTID=$(platform p:info id 2> /dev/null)
        fi
        printf "\n${CWARN}Connection to ${VCSSSHDOMAIN} failed!!!${CRESET}\n"
        printf "${CINFO}The connection test to ${VCSSSHDOMAIN} timed out. You will be unable to complete the remaining \n"
        printf "steps. Please contact ${DIGITALSERVICECONTACT} with the following information:\n"
        printf "${CBOLD}Failure${CRESET}${CINFO}: Connection test to ${VCSSSHDOMAIN}.${CRESET}\n"
        printf "${CBOLD}Project ID${CRESET}${CINFO}: ${PLATFORMPROJECTID}${CRESET}\n"
        printf "\n${CWARN}Exiting...${CRESET}\n"
        exit 1
    else
        printf "${CBOLD}Success!${CRESET}\n"
        printf "${CINFO}Now we can continue on to the composer build.${CRESET}\n"
    fi

else
    printf "${CBOLD}Success!${CRESET}\n"
fi