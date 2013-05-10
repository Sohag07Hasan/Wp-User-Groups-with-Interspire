<?php
/*
 * will handle interspire synchronization
 * */

class InterSpireSync{
	
	var $username;
	var $token;
	var $path;
	
	function __construct($options){
		if(is_array($options)){
			$this->username = $options['username'];
			$this->token = $options['token'];
			$this->path = $options['path'];
		}
	}
	
	
	/*get post handler*/
	function get_post_handler($url, $data, $headers = array()){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		
		if($header){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		
		return $ch;		
	}
	
	
	
	//make authentication request
	private function authRequest($method, $url, $data){
		switch($method){
			case 'POST':
				$ch = $this->get_post_handler($url, $data);
				break;				
		}
		
		//var_dump($ch);
		
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//$http = curl_getinfo($ch);
		curl_close($ch);
		
		return array(
			'http_code' => $http_code,
			'response'  => $response
		);
	}
	
	
	
	/*
	 * get the lists
	 * */
	function get_lists(){
		$lists = array();
		
		$xml = '<xmlrequest>
					<username>%s</username>
					<usertoken>%s</usertoken>
					<requesttype>lists</requesttype>
					<requestmethod>GetLists</requestmethod>
					<details>						
					</details>
				</xmlrequest>';
		
		$xml = $this->format_xml($xml);
		
		$request = $this->authRequest('POST', $this->path, $xml);

		if($request['http_code'] == 200){
			$xml = @ simplexml_load_string($request['response']);
			if($xml){
				if((string) $xml->status == 'SUCCESS'){
					if(isset($xml->data->item)){
						foreach($xml->data->item as $item){
						//	var_dump($item);							
							$lists[] = array(
								'listid' => (string) $item->listid,
								'name'    => (string) $item->name
							);
						}
					}
				}
			}
		}
		
		return $lists;
	}
	
	
	//format the string with the username and token
	function format_xml($xml){
		return sprintf($xml, $this->username, $this->token);
	}
	
}