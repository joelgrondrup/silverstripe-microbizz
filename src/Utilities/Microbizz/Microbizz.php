<?php

namespace JoelGrondrup\Microbizz;


use \Httpful\Request;

class Microbizz  {

    private $endpoint;

    private $contract;

    private $apiKey;
                
    private $username;

    private $password;

    private $accessToken;
    
    public function __construct($endpoint, $contract, $apiKey, $username, $password, $accessToken) {

        $this->endpoint = $endpoint;
        $this->contract = $contract;
        $this->apiKey = $apiKey;
        $this->username = $username;
        $this->password = $password;
        $this->accessToken = $accessToken;
        
    }

    private function makeCommand($command){

        return json_encode([
            "contract" => $this->contract,
            "apikey" => $this->apiKey,
            "username" => $this->username,
            "password" => $this->password,
            "accesstoken" => $this->accessToken,
            "commands" => [
                $command 
                ]
            ]
        );

    }

    private function makeRequest($string){

        $bodyString = "json=" . urlencode($string);

        $response = Request::post($this->endpoint)
            ->expectsJson()
            ->addHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8")
            ->body($bodyString)
            ->send();

        return $response;

    }

    public function query($query){

        $command = $this->makeCommand($query); 
        $response = $this->makeRequest($command);

        return $response;

    }

    public function validateSessionToken($token){

        $query = [
            "command" => "ValidateSessionToken",
            "sessiontoken" => $token
        ];

        return $this->query($query);

    }

}