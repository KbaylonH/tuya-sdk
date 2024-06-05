<?php namespace Kevinwbh\TuyaSdk\Services\SmartLock;

use Kevinwbh\TuyaSdk\Core\Client;

class DeviceUserApi {

    private Client $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public function addUser($device_id, $params){
        return $this->client->sendRequest('POST', '/v1.0/devices/' . $device_id . '/user', $params);
    }

    public function getUsers($device_id){
        return $this->client->sendRequest('GET', '/v1.0/devices/' . $device_id . '/users');
    }

}