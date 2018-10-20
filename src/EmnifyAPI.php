<?php

namespace BionConnection\EmnifyAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use \GuzzleHttp\Psr7\Uri;

class EmnifyAPI {

    const TIMEOUT = 10;
    const STATUS_SIM_NO_USADA = "0";
    const STATUS_EP_ENABLED = 0;
    const STATUS_EP_DISABLED = 1;
    const STATUS_EP_DELETED = 2;

    private $api_token = "";
    private $api_endpoint = "";
    private $auth_token;
    private $request_successful = false;
    private $last_error = '';
    private $last_response = array();
    private $last_request = array();
    private $period_valid_token = 5;
    private $arrSucess = ['response' => 'success'];

    public function __construct() {

        $this->auth_token = Cache::get('auth_token');
        $this->client = new Client(['http_errors' => false,
            'headers' => ['Content-Type' => 'application/json',]
        ]);

        $this->api_token = config('emnifyapi.api_token');
        $this->api_endpoint =config('emnifyapi.api_endpoint');
        $this->autenticate();
    }

    public function GetConnectionStatus($iccid) {

        $endPoint = $this->getEndPoint($iccid)[0];
        return $this->makeRequest("get", "endpoint/" . $endPoint->id . "/connectivity");
    }

    public function GetLocationStatus($iccid) {
        $endPoint = $this->getEndPoint($iccid)[0];
        return $this->makeRequest("get", "endpoint/" . $endPoint->id . "/connectivity_info");
    }

    public function changeEndPointServiceProfile($iccid, $new_id_service_profile) {
        $endPoint = $this->getEndPoint($iccid)[0];
        $arrUpdate = ["service_profile" => ["id" => $new_id_service_profile]];
        $this->updateEndPoint($id_end_point, $arrUpdate);
    }

    public function getSims($iccid = null, $inactive_new = false) {
        $query = "";

        if ($inactive_new) {
            $query = "status:" . STATUS_SIM_NO_USADA;
        }

        if (isset($iccid)) {
            $query .= "iccid:" . $iccid;
        }

        return $this->makeRequest("get", "sim?q=" . $query);
    }

    private function updateEndPoint($id_end_point, $arrUpdate) {
        $this->makeRequest("patch", "endpoint/" . $id_end_point, $arrUpdate);
    }

    private function relaceEndPointSim($id_end_point) {

        $this->updateEndPoint($id_end_point, ["sim" => ["id" => null]]);
    }

    private function getEndPoint($iccid = null, $inactive_new = false) {
        $query = "";

        if ($inactive_new) {
            $query = "status:" . STATUS_SIM_NO_USADA;
        }

        if (isset($iccid)) {
            $query .= "iccid:" . $iccid;
        }

        return $this->makeRequest("get", "endpoint?q=" . $query);
    }

    private function createEndPoint($id_service_profile, $id_sim) {
        $default_tariff_profile = 188198;
        $arrparams = ['name' => ' EndPoint Clien ' . $id_sim,
            'status' => ['id' => self::STATUS_EP_ENABLED],
            'service_profile' => ['id' => $id_service_profile],
            'tariff_profile' => ['id' => $default_tariff_profile],
            'sim' => ['id' => $id_sim, 'activate' => true]
        ];

        $this->makeRequest("post", "endpoint", $arrparams);

        return $this->request_successful;
    }

    public function activateSim($icc, $id_service_profile) {

        $sim = $this->getSims($icc)[0];


        if ($this->createEndPoint($id_service_profile, $sim->id)) {

            return $this->arrSucess;
        }
    }

    private function deleteEndPoint($iccid) {

        $endpoint = $this->getEndPoint($iccid)[0];
        $this->relaceEndPointSim($endpoint->id);
        $this->makeRequest("delete", "endpoint/" . $endpoint->id);

        if ($this->request_successful) {

            return $this->arrSucess;
        }
    }

    private function deleteSim($iccid) {
        $sim = $this->getSims($iccid)[0];
        $this->makeRequest("delete", "sim/" . $sim->id);


        if ($this->request_successful) {

            return $this->arrSucess;
        }
    }

    public function terminateSim($iccid) {

        $this->deleteEndPoint($iccid);

        $this->deleteSim($iccid);
    }

    private function autenticate() {
        if (!$this->auth_token) {
            $arrToken = array("application_token" => $this->api_token);
            $response = $this->client->post($this->getUrlApi('authenticate'), ['body' => json_encode($arrToken)]);
            switch ($response->getStatusCode()) {
                case 200:
                    $login = json_decode($response->getBody());
                    Cache::put('auth_token', $login->auth_token, $this->period_valid_token);
                    $this->auth_token = $login->auth_token;
                    break;
                case 401 :
                    $this->last_error = sprintf('%d: Token invalido', $response->getStatusCode());
                    break;
            }
        }
    }

    public function suspedSim($iccid) {

        $endPoint = $this->getEndPoint($iccid)[0];
        $arrUpdate = ["status" => self::STATUS_EP_DISABLED];
        $this->updateEndPoint($endPoint->id, $arrUpdate);
    }

    public function unSuspendSim($iccid) {
        $endPoint = $this->getEndPoint($iccid)[0];
        $arrUpdate = ["status" => self::STATUS_EP_ENABLED];
        $this->updateEndPoint($endPoint->id, $arrUpdate);
    }

    private function getUrlApi($method) {
        return $this->api_endpoint . '/' . $method;
    }

    private function makeRequest($http_verb, $method, $args = array(), $timeout = self::TIMEOUT) {
        unset($this->response);
        $this->request_successful = false;
        $headers = ['Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->auth_token];

        switch ($http_verb) {
            case 'post':
                $this->response = $this->client->post($this->getUrlApi($method), ['body' => json_encode($args), 'headers' => $headers]);

                break;
            case 'get':
                $this->response = $this->client->get($this->getUrlApi($method), ['headers' => $headers]);
                break;
            case 'delete':
                $this->response = $this->client->delete($this->getUrlApi($method), ['headers' => $headers]);

                break;
            case 'patch':
                $this->response = $this->client->patch($this->getUrlApi($method), ['body' => json_encode($args), 'headers' => $headers]);
                break;
            case 'put':
                // return $this->client->put($url, ['cert' => $this->uri_ca_pem, 'ssl_key' => $this->uri_key_pem, 'body' => $args, 'timeout' => $timeout])->getBody();

                break;
        }

        
        switch ($this->response->getStatusCode()) {
            case 200:
                $this->request_successful = true;
                return json_decode($this->response->getBody());
                break;
            case 401;
                $this->revokeToken();
                $this->makeRequest($http_verb, $method, $args = array(), $timeout = self::TIMEOUT);
                break;
            case 204: //borrado correctamente
                $this->request_successful = true;
                break;
            case 409:

            default :
                return json_decode($this->response->getBody());
                break;
        }
    }

    private function revokeToken() {
        $this->auth_token = false;
        $this->autenticate();
    }

    public function getLastError() {
        return $this->last_error ?: false;
    }

}
