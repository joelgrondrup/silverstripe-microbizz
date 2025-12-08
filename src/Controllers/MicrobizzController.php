<?php

namespace {

    use SilverStripe\CMS\Controllers\ContentController;

    class MicrobizzController extends ContentController
    {
        private static $allowed_actions = [
            'negotiate',
            'returnurl',
            'webhook',
            'interface'
        ];

        protected function init()
        {
            parent::init();
            
        }

        public function webhook(){

            $params = $this->getRequest()->params();
            
            $id = isset($params['ID']) ? $params['ID'] : false;
            $otherId = isset($params['OtherID']) ? $params['OtherID'] : false;

            //error_log('Microbizz hook fired with ID: ' . $id . " and OtherID: " . $otherId);

            $microbizzApplication = \MicrobizzApplication::get_by_id($id);

            if (!$microbizzApplication) {
                error_log('Microbizz application not found');
                return $this->httpError(200);
            }

            $microbizzHook = \MicrobizzHook::get_by_id($otherId);

            if (!$microbizzHook) {
                error_log('Microbizz hook not found');
                return $this->httpError(200);
            }

            $microbizzEvent = MicrobizzEvent::create();
            $microbizzEvent->ModCode = $microbizzHook->ModCode;
            $microbizzEvent->Hook = $microbizzHook->Hook;
            $microbizzEvent->MicrobizzApplication = $microbizzApplication->Title;
            $microbizzEvent->Contract = $microbizzApplication->Contract;
            $microbizzEvent->MicrobizzApplicationID = $microbizzApplication->ID;
            $microbizzEvent->MicrobizzHookID = $microbizzHook->ID;
            $microbizzEvent->POST = json_encode($_POST);

            $object = isset($_POST["object"]) ? $_POST["object"] : false;

            if ($object){

                $todo = json_decode($object);
                
                if (isset($todo->id))
                    $microbizzEvent->Todo = $todo->id;

            }

            $microbizzEvent->write();

            if (!empty($microbizzHook->Handle)){

                $handleArray = explode('::', $microbizzHook->Handle);
                $class = $handleArray[0];
                $function = $handleArray[1];

                if (class_exists($class) && method_exists($class, $function)){

                    $params = [
                        "application" => $microbizzApplication,
                        "event" => $microbizzEvent,
                        "hook" => $microbizzHook,
                        "contract" => $microbizzApplication->Contract,
                        "apikey" => $microbizzApplication->AccessToken,
                        "username" => $microbizzApplication->UserName,
                        "password" => $microbizzApplication->Password
                    ];

                    $class::$function($_POST, $params, $microbizzEvent);
                    //error_log("MicrobizzWebhoook handle fired with class: " . $class . " and static method: " . $function);

                }

            }
            else{
                error_log('MicrobizzWebhook reached, but no handle was fired');
            }

        }

        public function interface(){

            $params = $this->getRequest()->params();
            
            $id = isset($params['ID']) ? $params['ID'] : false;
            $otherId = isset($params['OtherID']) ? $params['OtherID'] : false;

            error_log('Microbizz interface fired with ID: ' . $id . " and OtherID: " . $otherId);

            print_r("HELLO WORLD");

            /*
            $microbizzApplication = \MicrobizzApplication::get_by_id($id);

            if (!$microbizzApplication) {
                error_log('Microbizz application not found');
                return $this->httpError(200);
            }

            $microbizzHook = \MicrobizzHook::get_by_id($otherId);

            if (!$microbizzHook) {
                error_log('Microbizz hook not found');
                return $this->httpError(200);
            }

            $microbizzEvent = MicrobizzEvent::create();
            $microbizzEvent->ModCode = $microbizzHook->ModCode;
            $microbizzEvent->Hook = $microbizzHook->Hook;
            $microbizzEvent->MicrobizzApplication = $microbizzApplication->Title;
            $microbizzEvent->Contract = $microbizzApplication->Contract;
            $microbizzEvent->MicrobizzApplicationID = $microbizzApplication->ID;
            $microbizzEvent->MicrobizzHookID = $microbizzHook->ID;
            $microbizzEvent->POST = json_encode($_POST);

            $object = isset($_POST["object"]) ? $_POST["object"] : false;

            if ($object){

                $todo = json_decode($object);
                
                if (isset($todo->id))
                    $microbizzEvent->Todo = $todo->id;

            }

            $microbizzEvent->write();

            if (!empty($microbizzHook->Handle)){

                $handleArray = explode('::', $microbizzHook->Handle);
                $class = $handleArray[0];
                $function = $handleArray[1];

                if (class_exists($class) && method_exists($class, $function)){

                    $params = [
                        "application" => $microbizzApplication,
                        "event" => $microbizzEvent,
                        "hook" => $microbizzHook,
                        "contract" => $microbizzApplication->Contract,
                        "apikey" => $microbizzApplication->AccessToken,
                        "username" => $microbizzApplication->UserName,
                        "password" => $microbizzApplication->Password
                    ];

                    $class::$function($_POST, $params, $microbizzEvent);
                    //error_log("MicrobizzWebhoook handle fired with class: " . $class . " and static method: " . $function);

                }

            }
            else{
                error_log('MicrobizzWebhook reached, but no handle was fired');
            }
            */

        }

        public function negotiate(){

            $params = $this->getRequest()->params();
            
            $ID = isset($params['ID']) ? $params['ID'] : false;

            if (!$ID) {
                error_log('Id missing in negotiate URL');
                return $this->httpError(404);
            }

            $MicrobizzApplication = \MicrobizzApplication::get_by_id($ID);

            if (!$MicrobizzApplication) {
                error_log('Microbizz application not found');
                return $this->httpError(404);
            }

            error_log(json_encode($_POST));

            $endpoint = isset($_POST["endpoint"]) ? $_POST["endpoint"] : false;
            $contract = isset($_POST["contract"]) ? $_POST["contract"] : false;
            $accesstoken = isset($_POST["accesstoken"]) ? $_POST["accesstoken"] : false;
            $challenge = isset($_POST["challenge"]) ? $_POST["challenge"] : false;

            error_log($endpoint);
            error_log($contract);
            error_log($accesstoken);
            error_log($challenge);

            if ($endpoint !== false && $contract !== false && $accesstoken !== false && $challenge !== false) {
                
                $MicrobizzApplication->EndPoint = $endpoint;
                $MicrobizzApplication->Contract = $contract;
                $MicrobizzApplication->AccessToken = $accesstoken;

                $MicrobizzApplication->write();
                error_log("Negotiate script succeded");
                error_log($challenge . $MicrobizzApplication->SecretKey);
                error_log(sha1($challenge . $MicrobizzApplication->SecretKey));

                return sha1($challenge . $MicrobizzApplication->SecretKey);

            } else {

                error_log('Missing post params from Microbizz');
                $this->Title = 'Missing post params from Microbizz';
                return $this->httpError(404);

            }

        }

        public function returnurl(){

            $params = $this->getRequest()->params();
            
            $ID = isset($params['ID']) ? $params['ID'] : false;

            if (!$ID) {
                error_log('Id missing in return URL');
                return $this->httpError(404);
            }

            $RedirectUrl = "/admin/microbizzapplications/MicrobizzApplication/EditForm/field/MicrobizzApplication/item/" . $ID . "/edit";

            header('Location: ' . $RedirectUrl);
            exit;

        }
        
    }
}
