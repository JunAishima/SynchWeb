#!/bin/bash
#
# This script is deprecated at DLS.
#
. /etc/profile.d/modules.sh
module load global/cluster

qsub -cwd -pe smp 16 -q medium.q@@com09 $1

