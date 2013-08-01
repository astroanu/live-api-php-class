<?php
/**
 * PHP Class for Microsoft Live API
 *
 * {@link https://github.com/astroanu/live-api-php-class live-api-php-class}
 * 
 * @author       Anuradha Jayathilaka <astroanu2004@gmail.com>
 * @version    0.1
 */

class LiveAPIClient{
	
	protected $_live_id;
	protected $_live_secret;
	protected $_redirect_url;
	protected $_access_token;
	protected $_scope;
	protected $_debug;
	
	/**
	 * @param string $guid 
	 * @return object
	 */
	public function getProfile($guid = 'me'){
		return $this->request('https://apis.live.net/v5.0/' . $guid, 'GET');
	}
	
	/**
	 * @param array $params
	 * @return object
	 */
	public function getContacts($params = array()){
		return $this->request('https://apis.live.net/v5.0/me/contacts', 'GET', $params);
	}
	
	function __construct($liveId, $liveSecret, $redirectUrl, $debug = true){
		$this->setLiveId($liveId) ;
		$this->setLiveSecret($liveSecret);
		$this->setRedirectUrl($redirectUrl);
		$this->setScopes('wl.offline_access,wl.signin,wl.basic');
		$this->_debug = $debug;
	}
	
	private function curlExec($url, $postFields){
		$headers = array('Content-Type: application/x-www-form-urlencoded');		
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS,  http_build_query($postFields));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		return curl_exec($ch);
	}
	
	/**
	 * @return Object
	 */
	public function refreshAccessToken(){
		$postFields = array(
				'grant_type' => 'refresh_token',
				'client_id' => $this->getLiveId(),
				'client_secret' => $this->getLiveSecret(),
				'redirect_uri' => $this->getRedirectUrl(),
				'refresh_token' => $this->getAccessToken('refresh_token')
		);
		
		try{
			$req = $this->curlExec('https://login.live.com/oauth20_token.srf', $postFields);
					
			if(is_object($responseObj = json_decode($req)) && property_exists($responseObj, 'access_token')){
				throw new LiveAPIException('ok', 200);
			}
			elseif(is_object($responseObj = json_decode($req)) && property_exists($responseObj, 'error')){
				throw new LiveAPIException($responseObj, 1001);
			}
			else{
				throw new LiveAPIException('Unknown Live API Exception', 1000);
			}
		}
		catch (LiveAPIException $e){
			switch ($e->getCode()) {
				case 200:
					error_log('refreshed');
					$responseObj->ts = time();
					$this->setAccessToken(json_encode($responseObj));
					return $this->getAccessToken();
					break;
		
				default:
					return false;
					break;
			}
		}		
	}
	
	/**
	 * @return Object
	 */
	public function fetchAccessToken(){
		try{			
			if(!isset($_GET['code'])){
				throw new LiveAPIException(__METHOD__ .'A code must be defined');
			}
			
			$postFields = array(
					'code' => $_GET['code'],
					'grant_type' => 'authorization_code',
					'client_id' => $this->getLiveId(),
					'client_secret' => $this->getLiveSecret(),
					'redirect_uri' => $this->getRedirectUrl()		
			);

			$req = $this->curlExec('https://login.live.com/oauth20_token.srf', $postFields);			
			
			if(is_object($responseObj = json_decode($req)) && property_exists($responseObj, 'access_token')){
				throw new LiveAPIException('ok', 200);
			}
			elseif(is_object($responseObj = json_decode($req)) && property_exists($responseObj, 'error')){
				throw new LiveAPIException($responseObj, 1001);
			}
			else{
				throw new LiveAPIException('Unknown Live API Exception', 1000);
			}			
		}
		catch (LiveAPIException $e){
			switch ($e->getCode()) {
				case 200:
					$responseObj->ts = time();
					$this->setAccessToken(json_encode($responseObj));
					return $this->getAccessToken();
					break;
				
				default:
					return false;
					break;
			}
		}		
	}
	
	/**
	 * @param boolean $returnUrl
	 * @return string if $returnUrl is true else redirect
	 */
	public function authorize($returnUrl = false){
		$url  = 'https://login.live.com/oauth20_authorize.srf?client_id='.$this->getLiveId().
				'&scope='.$this->getScopes().'&response_type=code&redirect_uri='.$this->getRedirectUrl();
		if($returnUrl){
			return $url;
		}
		header('Location: ' . $url );
	} 
	
	/**
	 * @param string $url
	 * @param string $method
	 * @param array $params
	 * @return object
	 */
	public function request($url, $method = 'GET', $params = array()) {
		if(intval($this->getAccessToken('ts')) + intval($this->getAccessToken('expires_in')) < time()){
			$this->refreshAccessToken();
		}
	    
	    try{
	    	$ch = curl_init($url .'?'. http_build_query($params));	    
	    		 
	    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	    	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->getAccessToken('access_token')));	
	    	    	 
	    	$response = curl_exec($ch);	    

	    	if(is_object($responseObj = json_decode($response)) && property_exists($responseObj, 'error')){
	    		throw new LiveAPIException($responseObj, 1000);
	    	}
	    	else{
	    		throw new LiveAPIException('ok', 200);
	    	}
	    }
	    catch(LiveAPIException $e){
	    	switch ($e->getCode()) {
	    		case 200:
	    			return $responseObj;
	    			break;
	    		
	    		default:
	    			return $e;
	    			break;
	    	}
	    }
	}
	
	public function setLiveId($liveId){
		$this->_live_id = $liveId;
	}
	
	public function getLiveId(){
		return $this->_live_id;
	}
	
	public function setLiveSecret($liveSecret){
		$this->_live_secret = $liveSecret;
	}
	
	public function getLiveSecret(){
		return $this->_live_secret;
	}

	public function setAccessToken($accessToken){
		if(is_string(func_get_args(0)) && is_object($tokenObj = json_decode($accessToken))){
			$this->_access_token = $tokenObj;
		}
		$this->_access_token = $accessToken;
	}
	
	public function getAccessToken(){
		if(is_object($tokenObj = json_decode($this->_access_token))){
			if(func_num_args() == 1 && is_string($portion = func_get_arg(0))){
				return $tokenObj->$portion;
			}
			elseif(func_num_args() == 1 && is_bool(func_get_arg(0)) && func_get_arg(0) == true){
				return $this->_access_token;
			}
			return $tokenObj;
		}
		return null;
	}	
	
	public function setRedirectUrl($redirectUrl){
		$this->_redirect_url = $redirectUrl;
	}
	
	public function getRedirectUrl(){
		return $this->_redirect_url;
	}
	
	public function setScopes(){
		if(is_array(func_get_arg(0))){
			$this->_scope = implode(',', func_get_arg(0));
		}
		elseif(is_string(func_get_arg(0))){
			$this->_scope = func_get_arg(0);
		}
	}
	
	public function getScopes(){
		return $this->_scope;
	}
}

class LiveAPIException extends Exception{
	public function __construct($except, $code = 0, Exception $previous = null) {
		if(is_object($except) && property_exists($except->error, 'code')){			
			parent::__construct($except->error->code . ' : ' . $except->error->message, $code, $previous);
		}
		elseif(is_object($except) && property_exists($except, 'error')){
			parent::__construct($except->error . ' : ' . $except->error_description, $code, $previous);
		}
		elseif(is_string($except)){
			parent::__construct($except, $code, $previous);
		}
		else{
			parent::__construct('Unknown Live API Error', 1001, $previous);
		}
	}
	
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}
?>
