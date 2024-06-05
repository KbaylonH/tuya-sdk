<?php namespace Kevinwbh\TuyaSdk\Core;

use GuzzleHttp\Client as GuzzleClient;

use Kevinwbh\TuyaSdk\Core\Config;

class Client {
    private $clientId;
    
    private $clientSecret;

    private $guzzleClient;

    private $accessToken = '';

    public function __construct(string $clientId, string $clientSecret, string $dataCenter = Config::CHINA_DATA_CENTER) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->guzzleClient = new GuzzleClient([
            'base_uri' => $dataCenter,
            'timeout'  => Config::HTTP_TIMEOUT,
        ]);
    }

    public function authenticate() {
        // Lógica de autenticación
    }

    public function sendRequest(string $method, string $endpoint, $body = null) {
        $t = time() * 1000;
        $stringRequest = $this->_getStringRequest($endpoint, $method, $body);
        $signedString = $this->_getSignedString($t, '', $stringRequest);

        $headers = [
            'Content-Type' => 'application/json',
            'client_id' => $this->clientId,
            't' => $t,
            'sign_method' => 'HMAC-SHA256',
            'sign' => $signedString
        ];

        if($this->accessToken !== ''){
            $headers['access_token'] = $this->accessToken;
        }


        $options = [
            'headers' => $headers
        ];

        if($body !== null){
            $options['json'] = $body;
        }

        $response = $this->guzzleClient->request($method, $endpoint, $options);

        $body = $response->getBody()->getContents();

        return json_decode($body);
    }

    public function generateAccessToken() {

        $t = time() * 1000;
		$endpoint = '/v1.0/token?grant_type=1';
        
        $stringRequest = $this->_getStringRequest($endpoint, 'GET');
        
        $signedString = $this->_getSignedString($t, '', $stringRequest);

        $headers = [
            'Content-Type' => 'application/json',
            'client_id' => $this->clientId,
            't' => $t,
            'sign_method' => 'HMAC-SHA256',
            'sign' => $signedString
        ];

        $response = $this->guzzleClient->request('GET', $endpoint, [
            'headers' => $headers,
        ]);

        $body = $response->getBody()->getContents();

        return json_decode($body);
    }

    public function getClientSecret(){
        return $this->clientSecret;
    }

    public function setAccessToken($accessToken){
        $this->accessToken = $accessToken;
        return $this;
    }

    private function _getStringRequest($endpoint, $method, $body = null, $headers = null)
    {
        $str = $method . "\n";

        $jsonBody = $body == null ? '' : json_encode($body);

        $str .= hash('sha256', $jsonBody) . "\n";

        if($headers !== null && is_array($headers)){
            ksort($headers);
            foreach($headers as $key => $value){
                $str .= $key . ':' . $value . "\n";
            }
        }

        $str .= "\n".$endpoint;

        return $str;
    }

    private function _getSignedString($t, $nonce, $stringRequest)
    {
        $str = $this->clientId . $this->accessToken . $t . $nonce . $stringRequest;
        $signed_str = hash_hmac('sha256', $str, $this->clientSecret);
        return strtoupper($signed_str);
    }
}
