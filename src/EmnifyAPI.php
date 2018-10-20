<?php

namespace BionConnection\EmnifyAPI;

class EmnifyAPI
{
    const TIMEOUT = 10;

    private $api_token = "eyJhbGciOiJIUzUxMiJ9.eyJlc2MuYXBwc2VjcmV0IjoiYzNkYWIzMTgtYzQ4Yy00MTY1LWI5OTgtNjA1MDZkNmEwZTkwIiwic3ViIjoiaXZhbi5zeXNAZ3J1cG9iaW9uLmNvbSIsImF1ZCI6IlwvYXBpXC92MVwvYXBwbGljYXRpb25fdG9rZW4iLCJlc2MuYXBwIjoxMzkwLCJhcGlfa2V5IjpudWxsLCJlc2MudXNlciI6MjAwMTc1LCJlc2Mub3JnIjo2MDEwLCJlc2Mub3JnTmFtZSI6IkdSVVBPIEJJT04iLCJpc3MiOiJzcGMtZnJvbnRlbmQwMDFAc3BjLWZyb250ZW5kIiwiaWF0IjoxNTQwMDQ5MTczfQ.Gnr_mioFAuEY1HWZdeIlkS7jSBuL1dpximPE9ZqUy6DhHTHYJ4p01kkFlE0USjNkAiXd1ltapAJlkHLm6rvMJA";
    private $api_endpoint = "https://cdn.emnify.net/api/v1";

    public function __construct() {

       /* $this->username  = config('emnifyapi.uri_key_pem');
        $this->uri_ca_pem = config('movistarm2m.uri_ca_pem');
        $this->response = null;
        $this->api_endpoint = config('movistarm2m.url');*/
        
        $this->client = new Client(['http_errors' => true]);
        
    }
    private function autenticate(){
        $arrToken = array("application_token"=> $this->api_token);
        
        print_r($this->makeRequest("GET","authenticate"));
        
    }
     private function makeRequest($http_verb, $method, $args = array(), $timeout = self::TIMEOUT) {
        unset($this->response);
        $url = $this->api_endpoint . '/' . $method;
        switch ($http_verb) {
            case 'post':
                break;
            case 'get':
                $this->response = $this->client->get($url, ['cert' => $this->uri_ca_pem, 'ssl_key' => $this->uri_key_pem, 'query' => $args, 'timeout' => $timeout])->getBody();
                return $this->response;
                break;
            case 'delete':

                break;
            case 'patch':

                break;
            case 'put':
                return $this->client->put($url, ['cert' => $this->uri_ca_pem, 'ssl_key' => $this->uri_key_pem, 'body' => $args, 'timeout' => $timeout])->getBody();

                break;
        }
    }

    

}