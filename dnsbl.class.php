<?php
/* 
	This class is inteded to provide easy access to the DNSbl lists 
	to help find open proxies and possible spammers
			I'll properly document the class later
*/

class DNSbl {
	
	public $defaultbl = 'dnsbl.dronebl.org';
	public $dnsbls = array();
	
	/*
		Simple lookup method
			Checks if a ip is in the BL or not
	*/
	public function lookup($ip,$bl='dnsbl.dronebl.org',$type='A'){
		/* This function will not check why a host is banned or do multiple
		checks but it will check if it exists which is normally enough. */
		$ip_parts_array = explode(".",$ip);
		$reverse_ip = ""; // Create it so we can add to it in the loop
		for ($ip_segment = 3; $ip_segment >= 0; $ip_segment--) {
			// Reverse all the segments and create a valid a
		    		$reverse_ip .= $ip_parts_array[$ip_segment].".";		
		}
		// Now we need to look up (even if its basic lookup
		$lookup = $reverse_ip.$bl;

		return checkdnsrr($lookup,$type);
	}

	/*
		Find the reason from the DNS lookup
	*/
	private function dnsbl_reason($dns_lookup){
		// We process the dns lookup and process loop till we get the first
		// result if we find none assume false
		$result = false;
		// This IS VERY BUGGY - Any thoughts would be helpful.
		for($i = 0; $i <= (count($dns_lookup)-1); $i++){
			if(isset($dns_lookup[$i]['txt'])){	
				return $dns_lookup[$i]['txt'];			
			}
		}
	}

	/*
		Converts ip address into the reverse address for dnsbl lookup
	*/
	protected function convert_ipaddr($ip){
		$ip_parts_array = explode(".",$ip);
		$reverse_ip = ""; // Create it so we can add to it in the loop
		// Loop through the list till we can convert the reverse of the ip
		for ($ip_segment = 3; $ip_segment >= 0; $ip_segment--) {
			// Reverse all the segments and create a valid a
		    		$reverse_ip .= $ip_parts_array[$ip_segment].".";		
		}
		// Now we need to look up (even if its basic lookup)
		return $reverse_ip;
	}
	
	/*
		Gets the default DNSbl from the variable defaultbl
	*/
	private function default_dnsbl(){
		return $this->defaultbl;
	}
	
	/*
		Used to look through the DNSbls and check if the ip is found
		in any. This is very dangerous because not all BLs might be 
		updated.
	*/
	private function lookup_loop($ip){
		$return_array = array();
		foreach($this->dnsbls as $bl){
			// Return the bool result of the lookup
			$return_array[$bl] = $this->lookup($ip,$bl);
		}
		return $return_array;
	}
	
	/*
		Function is used get the dnsbl lookups 
		
	*/
	public function dnsbls($list){
		if(is_array($list)){
			$this->dnsbls = $list;
		} else {
			return false;		
		}
	}
	/*
		method is used to check through a list of DNSbls
	
		This is very dangerous because not all BLs might be 
		updated.
	*/
	public function multi_check($ip,$return_array=TRUE){
		// Check for an array of DNSbls 
		// If none are found use the default
		if(!is_array($this->dnsbls)){
			$this->lookup($ip,$this->default_dnsbl());	
		} else {
			// We have an array and we loop through the results.
			return $this->lookup_loop($ip);
		}
	}

	/* 
		method used to check the reason for being banned
		it depends on getting accurate results from the DNS server
		use 
	*/
	public function dnsbl_type_check($ip,$bl=NULL,$type=DNS_ALL){
		if(!isset($bl)){
			$bl = $this->default_dnsbl();
		}
		$return_type = 'text';
		/* This function will not check why a host is banned or do multiple
		checks but it will check if it exists which is normally enough. */
		$lookup = $this->convert_ipaddr($ip).$bl;
		// Look up the dns record
		$result = dns_get_record($lookup,$type);
		// Check to make sure it worked
		//return $this->dnsbl_reason($result);
		if($result){
			// Perhaps in another version return option bool or text
			if($return_type == 'bool'){
				return true;		
			} else {
				// and default + return
				return $this->dnsbl_reason($result);
			}
		} else {
			// If the value is invalid, its returned false
			return false;	
		}
	}
}

?>
