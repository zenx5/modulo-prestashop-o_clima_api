<?php



class O_clima_apiAjaxModuleFrontController extends ModuleFrontController{
    
    public function initContent(){   

        

    	$lat = Tools::getValue('lat');
    	$lon = Tools::getValue('lon');
		$appid = Configuration::get('appid','');//eabc1f88c31ce0171102d8f3a3fc69da
		$units = Configuration::get('units'); //metric
		$url = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=$appid&units=$units";
		//consumo con curl
		
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
		switch ($units) {
			case 'standard': $units = array('temp' => 'K', 'speed' => 'meter/sec'); break;
			case 'metric': $units = array('temp' => 'ºC', 'speed' => 'meter/sec'); break;
			case 'imperial': $units = array('temp' => 'ºF', 'speed' => 'miles/hour'); break;
		}

    	
		echo $result;
		die;
		
    }

}