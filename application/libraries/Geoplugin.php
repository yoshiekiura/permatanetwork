<?php
/*
This PHP class is free software: you can redistribute it and/or modify
the code under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version. 

However, the license header, copyright and author credits 
must not be modified in any form and always be displayed.

This class is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

@author geoPlugin (gp_support@geoplugin.com)
@copyright Copyright geoPlugin (gp_support@geoplugin.com)
$version 1.01


This PHP class uses the PHP Webservice of http://www.geoplugin.com/ to geolocate IP addresses

Geographical location of the IP address (visitor) and locate currency (symbol, code and exchange rate) are returned.

See http://www.geoplugin.com/webservices/php for more specific details of this free service

*/

class Geoplugin {
	
	//the geoPlugin server
	public $host = 'http://www.geoplugin.net/php.gp?ip={IP}&base_currency={CURRENCY}';
		
	//the default base currency
	public $currency = 'USD';
	
	//initiate the geoPlugin publics
	public $ip = null;
	public $city = null;
	public $region = null;
	public $areaCode = null;
	public $dmaCode = null;
	public $countryCode = null;
	public $countryName = null;
	public $continentCode = null;
	public $latitude = null;
	public $longitude = null;
	public $currencyCode = null;
	public $currencySymbol = null;
	public $currencyConverter = null;
	
	function geoPlugin() {

	}
	
	public static function locate($ip = null) {
		
		global $_SERVER;
		
		if ( is_null( $ip ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		$gp = new Geoplugin();
		
		$host = str_replace( '{IP}', $ip, $gp->host );
		$host = str_replace( '{CURRENCY}', $gp->currency, $host );
		
		$data = array();
		
		$response = $gp->fetch($host);
		
		$data = unserialize($response);
		
		
		//set the geoPlugin vars
		

		$gp->ip = $ip;
		$gp->city = (isset($data['geoplugin_city'])) ? $data['geoplugin_city'] : NULL;
		$gp->region = (isset($data['geoplugin_region'])) ? $data['geoplugin_region'] : NULL;
		$gp->areaCode = (isset($data['geoplugin_areaCode'])) ? $data['geoplugin_areaCode'] : NULL;
		$gp->dmaCode = (isset($data['geoplugin_dmaCode'])) ? $data['geoplugin_dmaCode'] : NULL;
		$gp->countryCode = (isset($data['geoplugin_countryCode'])) ? $data['geoplugin_countryCode'] : NULL;
		$gp->countryName = (isset($data['geoplugin_countryName'])) ? $data['geoplugin_countryName'] : NULL;
		$gp->continentCode =  (isset($data['geoplugin_continentCode'])) ? $data['geoplugin_continentCode'] : NULL;
		$gp->latitude =  (isset($data['geoplugin_latitude'])) ? $data['geoplugin_latitude'] : NULL;
		$gp->longitude =  (isset($data['geoplugin_longitude'])) ? $data['geoplugin_longitude'] : NULL;
		$gp->currencyCode =  (isset($data['geoplugin_currencyCode'])) ? $data['geoplugin_currencyCode'] : NULL;
		$gp->currencySymbol =  (isset($data['geoplugin_currencySymbol'])) ? $data['geoplugin_currencySymbol'] : NULL;
		$gp->currencyConverter =  (isset($data['geoplugin_currencyConverter'])) ? $data['geoplugin_currencyConverter'] : NULL;
		
		return $gp;
	
	}
	
	function fetch($host) {

		if ( function_exists('curl_init') ) {
						
			//use cURL to fetch data
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'geoPlugin PHP Class v1.0');
			$response = curl_exec($ch);
			curl_close ($ch);
			
		} else if ( ini_get('allow_url_fopen') ) {
			
			//fall back to fopen()
			$response = file_get_contents($host, 'r');
			
		} else {

			trigger_error ('geoPlugin class Error: Cannot retrieve data. Either compile PHP with cURL support or enable allow_url_fopen in php.ini ', E_USER_ERROR);
			return;
		
		}
		
		return $response;
	}
	
	function convert($amount, $float=2, $symbol=true) {
		
		//easily convert amounts to geolocated currency.
		if ( !is_numeric($this->currencyConverter) || $this->currencyConverter == 0 ) {
			trigger_error('geoPlugin class Notice: currencyConverter has no value.', E_USER_NOTICE);
			return $amount;
		}
		if ( !is_numeric($amount) ) {
			trigger_error ('geoPlugin class Warning: The amount passed to geoPlugin::convert is not numeric.', E_USER_WARNING);
			return $amount;
		}
		if ( $symbol === true ) {
			return $this->currencySymbol . round( ($amount * $this->currencyConverter), $float );
		} else {
			return round( ($amount * $this->currencyConverter), $float );
		}
	}
	
	function nearby($radius=10, $limit=null) {

		if ( !is_numeric($this->latitude) || !is_numeric($this->longitude) ) {
			trigger_error ('geoPlugin class Warning: Incorrect latitude or longitude values.', E_USER_NOTICE);
			return array( array() );
		}
		
		$host = "http://www.geoplugin.net/extras/nearby.gp?lat=" . $this->latitude . "&long=" . $this->longitude . "&radius={$radius}";
		
		if ( is_numeric($limit) )
			$host .= "&limit={$limit}";
			
		return unserialize( $this->fetch($host) );

	}

	
}
