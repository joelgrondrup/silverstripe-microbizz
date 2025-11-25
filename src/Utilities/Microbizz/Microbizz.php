<?php

namespace {

    /*
    use SilverStripe\CMS\Controllers\ContentController;
    use SilverStripe\ORM\ArrayList;
    use SilverStripe\ORM\DataObject;
    use SilverStripe\View\Requirements;
    use SilverStripe\Security\Member;
    use SilverStripe\Security\Permission;
    */
    use \Httpful\Request;

    class Microbizz  {

        private $URI = 'https://system.microbizz.dk/api/endpoint.php';

        private $contract;

        private $api;
                    
        private $username;

        private $password;
        
        public function __construct($contract, $apikey, $username, $password, $URI = false) {

            $this->contract = $contract;
            $this->api = $apikey;
            $this->username = $username;
            $this->password = $password;
            
            if (!$URI){
                $this->URI = $URI;
            }

        }

        private function makeCommand($command){

            return json_encode([
                "contract" => $this->contract,
                "apikey" => $this->api,
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

            $response = Request::post($this->URI)
                ->expectsJson()
                ->addHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8")
                ->body($bodyString)
                ->send();

            return $response;

        }

        public function query($request, $params){

            $command = $this->makeCommand($request); 
            $response = $this->makeRequest($command);

            return $response;

        }

    }



}
