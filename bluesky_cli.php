<?php
/*
 * This is bluesky-cli connector library for PHP programmer.
 * 
 * Author: Praween AMONTAMAVUT (Hayakawa Laboratory)
 * E-mail: praween@hykwlab.org
 * Create date: 2015-10-01
 *
 * USAGE:
 * 	require('bluesky_cli.php');
 * 	$blueskyGateway = "http://$GATEWAYDOMAIN:$PORT";
 * 	$bluesky_cli = new Bluesky_cli($blueskyGateway, "guest", "guest");
 */

class Bluesky_cli{
	var $_blueskyGateway,
	    $_username,
	    $_password;
	function Bluesky_cli($blueskyGateway, $username, $password){
		$this->_blueskyGateway = $blueskyGateway;
		$this->_username = $username;
		$this->_password = $password;
	}

	function test(){
		// test login;
		$login_result_json = $this->login();
		$login_result_obj = json_decode($login_result_json);
		$login_result = $login_result_obj->{'ETLog'}->{'login'}->{'result'};
		//echo $login_result_json . "\r\n";
		echo "login result:" . $login_result . "\r\n";
		echo "\r\n";
		// test logout;
		$logout_result_json = $this->logout();
		$logout_result_obj = json_decode($logout_result_json);
		$logout_result = $logout_result_obj->{'ETLog'}->{'logout'}->{'result'};
		//echo $logout_result_json . "\r\n";
		echo "logout result:" . $logout_result . "\r\n";
		echo "\r\n";
		// test createBlueskyParam;
		echo $this->createBlueskyParam("ls", array("noneFix", "edconnected")) . "\r\n";

		// test blueskyGet;
		//echo $this->blueskyGet($this->createBlueskyParam("ls", array("noneFix", "edconnected"))) . "\r\n";
		echo "\r\n";
		// test list_ed and getsensors;
		$list_ed = $this->list_ed();
		for($i = 0; $i < count($list_ed); $i++){
			echo $list_ed[$i]->{'EDIP'} . "\r\n";
			$embeddedDeviceIP = $list_ed[$i]->{'EDIP'};
			$connStatus = $list_ed[$i]->{'connStatus'};
			if($connStatus === "online"){
				$sensorAllChannelDat = $this->getSensorDatByAdc($embeddedDeviceIP, "mcp3208");
				if($sensorAllChannelDat){
					echo "[";
					foreach($sensorAllChannelDat as $eachChannel){
						echo $eachChannel . ", ";
					}
					echo "]\r\n";
					echo $this->getSensorDatByAdcChannel($embeddedDeviceIP, "mcp3208", 0) . "\r\n";
				}
			}
		}
		/*echo "\r\n";
		// test getSensorDatByAdc;
		$sensorAllChannelDat = $this->getSensorDatByAdc("172.16.4.105", "mcp3208");
		echo "[";
		foreach($sensorAllChannelDat as $eachChannel){
			echo $eachChannel . ", ";
		}
		echo "]\r\n";
		echo $this->getSensorDatByAdcChannel("172.16.4.105", "mcp3208", 0) . "\r\n";*/
	}

	/**
	 * Using sensornetwork with bluesky API.
	 */
	function sensornetwork($opts){
		$params = $this->createBlueskyParam("sensornetwork", $opts);
		$doTheAPI = $this->blueskyGet($params);
		return $doTheAPI;
	}

	function getSensorDatByAdc($deviceIP, $adcmodule){
		$mosi = "10";
		$miso = "9";
		$clk = "11";
		$ce = "8";
		$spiDat = null;
		$opts = array($deviceIP, "spi", $adcmodule, $mosi, $miso, $clk, $ce);
		$sensorDat = null;
		
		$sensorDat = $this->sensornetwork($opts);
		if($sensorDat){
			$spiDat = json_decode($sensorDat)->{'ETLog'}->{'logging'}->{'spi'};
		}
		return $spiDat;
	}

	function getSensorDatByAdcChannel($deviceIP, $adcmodule, $ch){
		$ret = null;
		$allChannelDat = $this->getSensorDatByAdc($deviceIP, $adcmodule);
		if($ch >= count($allChannelDat)){
			return $ret;
		}else if(is_array($allChannelDat)){
			$ret = $allChannelDat[$ch];
			return $ret;
		}else{
			return $ret;
		}
	}

	/**
	 * Return the list of connecting embedded devices information.
	 */
	function list_ed(){
		$blueskyParam = $this->createBlueskyParam("ls", array("noneFix", "edconnected"));
		$get_params = array
					(
						'http' => array
						(
							'method' => 'GET',
							'header'=>"Content-Type: application/json\r\n"
						)
					);
		$context = stream_context_create($get_params);
		$res = file_get_contents($this->_blueskyGateway . $blueskyParam, false, $context);
		return json_decode($res)->{'ETLog'}->{'EDConnStatement'};
	}

	/**
	 * Convert to parameter of HTTP.
	 */
	function createBlueskyParam($instruction, $opts){
		$ret = "/etLog?instruction=" . $instruction;
		if(is_array($opts)){
			$i = 1;
			foreach($opts as $opt){
				$ret .= "&opt" . $i . "=" . $opt;
				$i++;
			}
			return $ret;
		}else{
			return null;
		}
	}
	
	/**
	 * Do something with Bluesky API.
	 */
	function blueskyGet($blueskyParam){
		if(json_decode($this->login())->{'ETLog'}->{'login'}->{'result'} === "true"){
		}

		$get_params = array
					(
						'http' => array
						(
							'method' => 'GET',
							'header'=>"Content-Type: application/json\r\n"
						)
					);
		$context = stream_context_create($get_params);
		$res = file_get_contents($this->_blueskyGateway . $blueskyParam, false, $context);
		
		if(json_decode($this->logout())->{'ETLog'}->{'logout'}->{'result'} === "true"){
		}
		return $res;
	}

	/**
	 * login to the system as the public account.
	 */
	function login(){
		$login_result = null;
		$login_contents = array
					(
					'username' => $this->_username,
					'password' => $this->_password,
					'mode' => 'signin'
					);
		$login_params = array
					(
						'http' => array
						(
							'method' => 'POST',
							'header'=>"Content-Type: application/x-www-form-urlencoded\r\n",
							'content' => http_build_query($login_contents)
						)
					);
		for($i = 0; $i < 2; $i++){
			$context = stream_context_create($login_params);
			$login_result = file_get_contents($this->_blueskyGateway . '/doLogin.ins', false, $context);
		}
		return $login_result;
	}

	/**
	 * logout from the system from the account.
	 */
	function logout(){
		$logout_result = null;
		$logout_contents = array
					(
					'username' => $this->_username,
					'mode' => 'signout'
					);
		$logout_params = array
					(
						'http' => array
						(
							'method' => 'POST',
							'header'=>"Content-Type: application/x-www-form-urlencoded\r\n",
							'content' => http_build_query($logout_contents)
						)
					);
		for($i = 0; $i < 2; $i++){
			$context = stream_context_create($logout_params);
			$logout_result = file_get_contents($this->_blueskyGateway . '/doLogout.ins', false, $context);
		}
		return $logout_result;
	}
}
?>
