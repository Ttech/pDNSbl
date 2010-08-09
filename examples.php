<?php
	/* EXAMPLES 
		I will add some better examples a bit later
	*/
	include 'dnsbl.class.php';
	
	$ip = "127.0.0.1"; // Put an example ip here

	$dnsbl = new DNSbl;
	if($dnsbl->lookup($ip)){
		echo "This ip is found";
	} else {
		echo "This ip is not found in the DNSbl.";	
	}
	
	// Lookup a Reason
	$reason = $dnsbl->dnsbl_type_check($ip);
	if($reason){
		echo "IP is banned for ".$reason;
	}

	// Try doing a multi-lookup
	
	$lookups = array(
	'dnsbl.dronebl.org',
	'dnsbl.efnetrbl.org',
	'http.dnsbl.sorbs.net',
	'xbl.spamhaus.org'
	);
	$dnsbl->dnsbls($lookups);
	print_r($dnsbl->multi_check($ip));

?>
