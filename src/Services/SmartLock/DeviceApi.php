<?php namespace Kevinwbh\TuyaSdk\Services\SmartLock;

use Kevinwbh\TuyaSdk\Core\Client;

class DeviceApi {

    private Client $client;

    private string $device_id;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public function setDeviceId($device_id)
    {
        $this->device_id = $device_id;
    }

    public function getDetails(){
        return $this->client->sendRequest('GET', '/v1.0/devices/' . $this->device_id);
    }

    public function getSignalStrength($type = 'wifi'){
        return $this->client->sendRequest('GET', '/v2.0/cloud/thing/' . $this->device_id . '/' . $type . '/signal');
    }

    public function getStatus(){
        return $this->client->sendRequest('GET', '/v1.0/iot-03/devices/' . $this->device_id . '/status');
    }

    public function sendSignalDetection($type = 'WiFi'){
        return $this->client->sendRequest('POST', '/v2.0/cloud/thing/signal/detection/issue', [
            'device_id' => $this->device_id,
            'device_type' => $type
        ]);
    }

}