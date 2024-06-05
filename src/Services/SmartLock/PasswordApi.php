<?php namespace Kevinwbh\TuyaSdk\Services\SmartLock;

use Kevinwbh\TuyaSdk\Core\Client;

class PasswordApi 
{

    private $client;

    private $device_id;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public function assignPassword($device_id, $user_id, $no, $type = 'password')
    {
        $response = $this->client->sendRequest('POST', '/v1.0/devices/' . $device_id . '/device-lock/users/'.$user_id.'/allocate', [
            'no' => $no,
            'type' => $type
        ]);

        return $response->result;
    }

    public function getTemporaryKey()
    {
        $response = $this->client->sendRequest('POST', '/v1.0/devices/' . $this->device_id . '/door-lock/password-ticket');

        return $response->result;
    }


    public function enrollPasswordUnlocking($device_id, $user_id, $password, $ticket, $user_type = 2)
    {
        $encryptedPassword = $this->_encryptPasswordWithTicket($password, $ticket);

        return $this->client->sendRequest('PUT', '/v1.0/devices/' . $device_id . '/door-lock/actions/entry', [
            'unlock_type' => 'password',
            'user_type' => $user_type,            
            'user_id' => $user_id,
            'password' => $encryptedPassword,
            'password_type' => 'ticket',
            'ticket_id' => $ticket->ticket_id
        ]);
    }

    /**
     * Create a temporary password
     * See https://developer.tuya.com/en/docs/cloud/smart-door-lock?id=K9jgsgd4cgysr#title-11-Create%20a%20temporary%20password
     * 
     * @param string $name The name of the password
     * @param string $password The password
     * @param float $effective_time The 10-digit timestamp of the effective time. Unit: seconds (s).
     * @param float $invalid_time The 10-digit timestamp of the invalid time. Unit: seconds (s).
     * @param int $type Indicates the number of times a password can be used. Valid values: 1: password can be used once; 0: password can be used multiple times.
     * @param string $time_zone The time zone.
     * @param array $schedule_list The time period during which the password is valid.
     * 
     * @return object
     */
    public function createTemporaryPassword($name, $password, $effective_time, $invalid_time, $type, $time_zone, $schedule_list)
    {
        $ticket = $this->getTemporaryKey();
        
        $encryptedPassword = $this->_encryptPasswordWithTicket($password, $ticket);

        return $this->client->sendRequest('POST', '/v2.0/devices/'.$this->device_id.'/door-lock/temp-password', [
            'name' => $name, 
            'password' => $encryptedPassword,
            'effective_time' => $effective_time,
            'invalid_time' => $invalid_time,
            'password_type' => 'ticket',
            'ticket_id' => $ticket->ticket_id,
            'type' => $type,
            'time_zone' => $time_zone,
            'schedule_list' => $schedule_list
        ]);
    }

        /**
     * Create a unamed temporary password
     * See https://developer.tuya.com/en/docs/cloud/smart-door-lock?id=K9jgsgd4cgysr#title-12-Create%20an%20unnamed%20temporary%20password
     * 
     * @param string $password The password
     * @param float $effective_time The 10-digit timestamp of the effective time. Unit: seconds (s).
     * @param float $invalid_time The 10-digit timestamp of the invalid time. Unit: seconds (s).
     * @param int $type Indicates the number of times a password can be used. Valid values: 1: password can be used once; 0: password can be used multiple times.
     * @param string $time_zone The time zone.
     * @param array $schedule_list The time period during which the password is valid.
     * 
     * @return object
     */
    public function createUnamedTemporaryPassword($password, $effective_time, $invalid_time, $type, $timezone, $schedule_list)
    {
        $ticket = $this->getTemporaryKey();
        
        $encryptedPassword = $this->_encryptPasswordWithTicket($password, $ticket);

        return $this->client->sendRequest('POST', '/v2.0/devices/'.$this->device_id.'/door-lock/temp-password', [
            'password' => $encryptedPassword,
            'effective_time' => $effective_time,
            'invalid_time' => $invalid_time,
            'password_type' => 'ticket',
            'ticket_id' => $ticket->ticket_id,
            'type' => $type,
            'time_zone' => $timezone,
            'schedule_list' => $schedule_list
        ]);
    }

    public function setDeviceId($device_id)
    {
        $this->device_id = $device_id;
    }

    private function _decryptTicketKey($ticket)
    {
        $clientSecret = $this->client->getClientSecret();
        $key = hex2bin($ticket->ticket_key);
		$cipherMethod = 'aes-256-ecb';
		$options = OPENSSL_RAW_DATA;
		$claveUtf8 = utf8_encode($clientSecret);
	
		return openssl_decrypt($key, $cipherMethod, $claveUtf8, $options);
    }

    private function _encryptPasswordWithTicket($password, $ticket)
    {
        $decriptedKey = $this->_decryptTicketKey($ticket);
        $llaveDesencriptadaHex = bin2hex($decriptedKey);
	
		$cipherMethod = 'aes-128-ecb';
		$options = OPENSSL_RAW_DATA;
	
		$binaryPassword = openssl_encrypt($password, $cipherMethod, hex2bin($llaveDesencriptadaHex), $options);
	
		if ($binaryPassword === false) {
			return false;
		}
	
		$encryptedPassword = bin2hex($binaryPassword);
	
		return $encryptedPassword;
    }
}