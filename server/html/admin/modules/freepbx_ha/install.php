<?php

// Do we already have a Cluster running on this node?

exec("/usr/sbin/pcs status 2>&1", $out, $ret);
if ($ret == 0) {
	touch("/etc/schmooze/pbx-ha");
} else {
	@unlink("/etc/schmooze/pbx-ha");
}

