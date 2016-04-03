#!/bin/bash

# Upgrade PCS safely on this node.

# Ensure this actually IS a cluster node.
CLUSTERED=no
[ -e /usr/sbin/pcs ] && pcs status > /dev/null 2>&1 && CLUSTERED=yes

if [ "$CLUSTERED" == "no" ]; then
	echo "This doesn't appear to be a cluster node. Aborting before"
	echo "I break something."
	exit;
fi

# Sanity check existing file system configuration
[ ! -h /var/log/asterisk ] && ( rm -rf /var/log/asterisk; ln -s /drbd/asterisk/log /var/log/asterisk )
[ ! -h /var/spool/asterisk ] && ( rm -rf /var/spool/asterisk ; ln -s /drbd/asterisk/spool /var/spool/asterisk )
[ ! -h /var/run/asterisk ] && ( rm -rf /var/run/asterisk; ln -s /drbd/asterisk/run /var/run/asterisk )
[ ! -h /var/lib/asterisk ] && ( rm -rf /var/lib/asterisk; ln -s /drbd/asterisk/lib /var/lib/asterisk )
[ ! -h /etc/asterisk ] && ( rm -rf /etc/asterisk; ln -s /drbd/asterisk/etcasterisk /etc/asterisk )

if [ `/bin/pwd` != "/tmp" ] 
then
	echo -n "Copying files to /tmp..."
	/bin/cp -f Upgrade.repo /tmp
	/bin/cp -f files/cluster.py /tmp
	/bin/cp -f upgradecluster.sh /tmp
	echo "Done"
	echo -n "Copying files to other node..."
	SCP="-q -o ConnectTimeout=1 -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -o PreferredAuthentications=publickey -i /home/asterisk/.ssh/id_rsa "
	OTHER='freepbx-b'
	[ "`hostname`" == "freepbx-b" ] && OTHER='freepbx-a'
	`scp $SCP Upgrade.repo files/cluster.py upgradecluster.sh asterisk@$OTHER:/tmp`  
	echo "Done"
	echo "Please SSH to other (standby) node as root and run /tmp/upgradecluster.sh"
	exit 0
fi

if [ "`id -u`" != "0" ]
then
	echo "You must run this upgrade script as root."
	exit 1
fi

# First, make sure that this node is offline or in standby.
if crm_mon -1 | grep `hostname` | grep Online > /dev/null 
then
	echo "This node is ONLINE. You must set the current node to STANDBY before continuing"
	echo "You can use the FreePBX-HA GUI To do this, or run the command:"
	echo "   crm_standby -v on -N `hostname`"
	echo "from the command line."
	exit 78  # Configuration Error
fi

# Give the user a last chance to bail out.
echo "This will update Pacemaker and other associated programs. After this "
echo "is complete, this node will reboot automatically.  This is your last "
echo "chance to abort. ARE YOU SURE YOU WISH TO CONTINUE?"
echo -n "Continue? [Ny]: "
read RES

# Ensure it's in lower case
LC=`echo $RES | tr [:upper:] [:lower:]`
[ $RES != "y" -a $RES != "yes" ] && exit 0

# Maintance mode isn't required.

#echo -n "Putting the cluster in maintenance mode..."
#pcs property set maintenance-mode=true
#if [ $? -ne 0 ]
#then
#	echo "Failed!"
#	echo "Unknown cluster error when putting cluster in maintance mode."
#	echo "Please contact support"
#	exit 70 # Internal Error
#fi
#echo Done.
#
#echo -n "Waiting for all resources to switch to maintance mode..."
#OUT=`crm_mon -1 | egrep '(Started|Master)' | awk '{ print $1"="$5}' | grep -v '\(unmanaged\)'`
#if [ -n $OUT ]
#then
#	sleep 1
#	OUT=`crm_mon -1 | egrep '(Started|Master)' | awk '{ print $1"="$5}' | grep -v '\(unmanaged\)'`
#	if [ -n $OUT ]
#	then
#		# It's still not null. Something bad has happened.
#		echo "Failed!"
#		echo "Resources aren't switching to unmanaged mode."
#		echo "Please contant support"
#		exit 70 # Internal Error
#	fi
#fi
#echo Done.

echo "Upgrading core cluster packages:"
yum -y -c ./Upgrade.repo --enablerepo pcs upgrade pacemaker cman ccs resource-agents pcs

echo -n "Checking for required patches..."
S=`stat -c%s /usr/lib/python2.6/site-packages/pcs/cluster.py`
if [ $S -eq 25207 ]
then
	/bin/cp -f ./cluster.py /usr/lib/python2.6/site-packages/pcs/cluster.py
	echo "cluster.py patched"
else
	echo "Not required"
fi
echo "Rebooting."

sync
/sbin/reboot -f

