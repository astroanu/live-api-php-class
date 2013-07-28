<?php
class LiveAPIClient{
	
	protected $_live_id;
	protected $_live_secret;
	protected $_redirect_url;
	protected $_access_token;
	protected $_scope;
	
	function __construct($liveId, $liveSecret, $redirectUrl){
		$this->setLiveId($liveId) ;
		$this->setLiveSecret($liveSecret);
		$this->setRedirectUrl($redirectUrl);
		$this->setScopes('wl.basic,wl.contacts_emails');
	}
	
	public function fetchAccessToken(){
		$postFields = array(
			'code' => isset($_GET['code']) ? $_GET['code'] : '',
			'grant_type' => 'authorization_code',
			'client_id' => $this->getLiveId(),
			'client_secret' => $this->getLiveSecret(),
			'redirect_uri' => $this->getRedirectUrl(),				
		);
		
		$ch = curl_init('https://login.live.com/oauth20_token.srf');		
		$bodyData = http_build_query($postFields);		 
		$headers = array(
			'Content-Type: application/x-www-form-urlencoded'
		);		
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		 
		$req = curl_exec($ch);		
		
		if(is_object($token = json_decode($req)) && property_exists($token, 'access_token')){
			$this->_access_token = $token->access_token;
			return $token;
		}		
		return false;
	}
	
	public function authorize($returnUrl = false){
		$url  = 'https://login.live.com/oauth20_authorize.srf?client_id='.$this->getLiveId().
				'&scope='.$this->getScopes().'&response_type=code&redirect_uri='.$this->getRedirectUrl();
		if($returnUrl){
			return $url;
		}
		header('Location: ' . $url );
	} 
	
	public function request($url, $method = 'GET', $params = array()) {
		$postFields = array(
			'client_id' => $this->_live_id,
			'client_secret' => $this->_live_secret
		);
		
	    $bodyData = http_build_query($postFields);	    
	    $headers = array('Authorization: Bearer ' . $this->_access_token);
	    $ch = curl_init($url .'?'. http_build_query($params));
	    
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    
	    $response = curl_exec($ch);
	    
	  	if(is_object($responseObj = json_decode($response))){
	  		return $responseObj;
	  	}
		return false;	    
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
		$this->_access_token = $accessToken;
	}
	
	public function getAccessToken(){
		return $this->_access_token;
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
		}elseif(is_string(func_get_arg(0))){
			$this->_scope = func_get_arg(0);
		}
	}
	
	public function getScopes(){
		return $this->_scope;
	}
}

?>