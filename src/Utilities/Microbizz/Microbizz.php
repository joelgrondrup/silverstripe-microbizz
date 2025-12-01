<?php

namespace {

    use \Httpful\Request;

    class Microbizz  {

        private $endpoint;

        private $contract;

        private $apiKey;
                    
        private $username;

        private $password;
        
        public function __construct($endpoint, $contract, $apikey, $username, $password) {

            $this->endpoint = $endpoint;
            $this->contract = $contract;
            $this->apiKey = $apikey;
            $this->username = $username;
            $this->password = $password;
            
        }

        private function makeCommand($command){

            return json_encode([
                "contract" => $this->contract,
                "apikey" => $this->apiKey,
                "username" => $this->username,
                "password" => $this->password,
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

        public function query($request){

            $command = $this->makeCommand($request); 
            $response = $this->makeRequest($command);

            return $response;

        }

    }

}