<?php
    
    $apiKey = ''; // Ganti Api Key Disini
    $uri = 'https://pro.rajaongkir.com/api';
	
	if($apiKey == ''){
		echo "Masukan ApiKey terlebih dahulu, edit file index.php";
		exit();
	}

	$timeStart = microtime(true);

	$headers = [];
	$headers[] = 'key: ' . $apiKey;

	// empty file
	file_put_contents("province.csv", "");
	file_put_contents("city.csv", "");
	file_put_contents("subdistrict.csv", "");

	//Create Stream File
	$provinceFile = fopen("province.csv", "a+");
	$cityFile = fopen("city.csv", "a+");
	$subdistrictFile = fopen("subdistrict.csv", "a+");
	
	fwrite($provinceFile, 'id,name' . "\n");
	fwrite($cityFile, 'id,name,type,postal_code,province_id' . "\n");
	fwrite($subdistrictFile, 'id,name,province_id,city_id' . "\n");

	$exec = curl($uri . '/province', null, $headers, "GET");
	$dataProvince = json_decode($exec[3]);
	
	foreach($dataProvince->rajaongkir->results as $each){
		fwrite($provinceFile, $each->province_id . ',' . $each->province . "\n");
		
		$exec = curl($uri . '/city?province=' . $each->province_id, null, $headers, "GET");
		$dataCity = json_decode($exec[3]);
		
		foreach($dataCity->rajaongkir->results as $eachCity){
			fwrite($cityFile, $eachCity->city_id
				. ',' . $eachCity->city_name
				. ',' . $eachCity->type
				. ',' . $eachCity->postal_code
				. ',' . $eachCity->province_id . "\n");

			$exec = curl($uri . '/subdistrict?city=' . $eachCity->city_id, null, $headers, "GET");
			$dataSubdistrict = json_decode($exec[3]);

			foreach($dataSubdistrict->rajaongkir->results as $eachSubdistrict){
				fwrite($subdistrictFile, 
						$eachSubdistrict->subdistrict_id
						. ',' . $eachSubdistrict->subdistrict_name
						. ',' . $eachSubdistrict->province_id
						. ',' . $eachSubdistrict->city_id
						. "\n"
					);
			}
		}
	}

	$timeEnd = microtime(true);
	$timeUsed = ($timeEnd-$timeStart)/60;

	echo "Execution Time : " . $timeUsed . " Minutes";


        

    function curl($url, $fields = null, $headers = null, $custreq = null, $folloc = null){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				if($folloc !== null){
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				}
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_ENCODING , "gzip");
				curl_setopt($ch, CURLOPT_HEADER, true);
				
				if ($fields !== null) {
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
				}
				if ($headers !== null) {
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				}
				if ($custreq !== null) {
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $custreq);
				}else{
					curl_setopt($ch, CURLOPT_POST, 1);
				}
				if(curl_errno($ch)){
					echo 'Curl error: ' . curl_error($ch);
				}
				$result   = curl_exec($ch);
				$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$header = substr($result, 0, $header_size );
				$body = substr($result, $header_size );
				curl_close($ch);

				return array(
					$result,
					$httpcode,
					$header,
					$body,
				);
	}
?>
