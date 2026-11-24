<?php

namespace JoelGrondrup\Microbizz;

use SilverStripe\CMS\Controllers\ContentController;
use JoelGrondrup\Microbizz\MicrobizzHook;
use JoelGrondrup\Microbizz\MicrobizzApplication;

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

        $microbizzApplication = MicrobizzApplication::get_by_id($id);

        if (!$microbizzApplication) {
            error_log('Microbizz application not found');
            return $this->httpError(200);
        }

        $microbizzHook = MicrobizzHook::get_by_id($otherId);

        if (!$microbizzHook) {
            error_log('Microbizz hook not found');
            return $this->httpError(200);
        }

        $microbizzEvent = MicrobizzEvent::create();
        $microbizzEvent->ModCode = $microbizzHook->ModCode;
        $microbizzEvent->Hook = $microbizzHook->Hook;
        $microbizzEvent->MicrobizzApplication = $microbizzApplication->Title;
        $microbizzEvent->Contract = $microbizzApplication->Contract;
        // MicrobizzApplicationID is a Text field, and Silverstripe 6 rejects non-strings on write.
        $microbizzEvent->MicrobizzApplicationID = (string) $microbizzApplication->ID;
        $microbizzEvent->MicrobizzHookID = $microbizzHook->ID;
        $microbizzEvent->POST = json_encode($_POST);

        $object = isset($_POST["object"]) ? $_POST["object"] : false;

        if ($object){

            $decodedObject = json_decode($object);
            
            if (isset($decodedObject->id))
                $microbizzEvent->ObjectID = $decodedObject->id;

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
                    "endpoint" => $microbizzApplication->EndPoint,
                    "contract" => $microbizzApplication->Contract,
                    "apikey" => $microbizzApplication->AccessToken,
                    "username" => $microbizzApplication->UserName,
                    "password" => $microbizzApplication->Password,
                    "accesstoken" => $microbizzApplication->AccessToken
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

        // Interface pages are personal (per user, per session token): never let the app's
        // web view or a proxy cache them. Clients that put the token in the path got a
        // cached empty page back as "304 Not Modified" over and over.
        \SilverStripe\Control\Middleware\HTTPCacheControlMiddleware::singleton()->disableCache(true);

        $params = $this->getRequest()->params();

        // Query parameters, wherever the Microbizz app put them. Some app versions append
        // "&sessiontoken=..." to the interface URL without a "?", which lands the whole
        // query inside the last path segment (OtherID = "8&sessiontoken=..."). Split such
        // a tail off and parse it; the regular query string and POST still win.
        $extra = [];
        foreach (['ID', 'OtherID'] as $key) {
            if (isset($params[$key]) && preg_match('/^(\d+)[&;?](.*)$/s', (string)$params[$key], $match)) {
                $params[$key] = $match[1];
                parse_str($match[2], $parsed);
                $extra = array_merge($extra, $parsed);
            }
        }
        $query = array_merge($extra, $_POST, $_GET);

        $id = isset($params['ID']) ? $params['ID'] : false;
        $otherId = isset($params['OtherID']) ? $params['OtherID'] : false;
        $sessionToken = isset($query['sessiontoken']) ? $query['sessiontoken'] : false;

        // The URL shape and parameter names only — never the values (the session token).
        $path = preg_replace('/(sessiontoken|accesstoken)=[^&\/]*/i', '$1=***', (string)($_SERVER['REQUEST_URI'] ?? ''));
        error_log('Interface fired: ' . $path
            . ' · GET ' . implode(',', array_keys($_GET))
            . ' · POST ' . implode(',', array_keys($_POST))
            . ($extra ? ' · fra stien: ' . implode(',', array_keys($extra)) : '')
            . ' · UA ' . substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 120));

        $microbizzApplication = MicrobizzApplication::get_by_id((int)$id);

        if (!$microbizzApplication) {
            error_log('Microbizz application not found');
            return $this->httpError(200);
        }

        //Let's setup the security here
        $microbizz = new Microbizz(
            $microbizzApplication->EndPoint, 
            $microbizzApplication->Contract, 
            $microbizzApplication->APIKey, 
            $microbizzApplication->UserName, 
            $microbizzApplication->Password, 
            $microbizzApplication->AccessToken);
        
        // Never log the token itself: anyone with the log could reuse it until it expires.
        if (!$sessionToken) {
            error_log('Sessiontoken missing in request');
            return $this->httpError(403, 'Mangler login fra Microbizz (sessiontoken).');
        }

        $result = $microbizz->validateSessionToken($sessionToken);

        // status 1 only means the API call worked; the token is valid only when the
        // command result says so ("result": true). An invalid token still comes back
        // with status 1 and some userid, so status alone must never be trusted.
        $tokenResult = $result->body->results[0] ?? null;
        $tokenValid = isset($result->body->status) && $result->body->status == 1
            && isset($tokenResult->status) && $tokenResult->status == 1
            && isset($tokenResult->result) && in_array($tokenResult->result, [true, 1, '1'], true);

        if (!$tokenValid){
            error_log('Sessiontoken not validated: ' . json_encode($result->body ?? null));
            return $this->httpError(403, 'Login fra Microbizz kunne ikke bekræftes.');
        }

        error_log('Sessiontoken validated for user ' . ($tokenResult->userid ?? '?'));

        $microbizzInterface = MicrobizzInterface::get_by_id((int)$otherId);

        if (!$microbizzInterface) {
            error_log('Microbizz interface not found');
            return $this->httpError(200);
        }

        if (!empty($microbizzInterface->Handle)){

            $handleArray = explode('::', $microbizzInterface->Handle);
            $class = $handleArray[0];
            $function = $handleArray[1];

            if (class_exists($class) && method_exists($class, $function)){

                $params = [
                    "application" => $microbizzApplication,
                    "sessiontokenresult" => $result->body,
                    "interface" => $microbizzInterface,
                    "endpoint" => $microbizzApplication->EndPoint,
                    "contract" => $microbizzApplication->Contract,
                    "apikey" => $microbizzApplication->AccessToken,
                    "username" => $microbizzApplication->UserName,
                    "password" => $microbizzApplication->Password,
                    "accesstoken" => $microbizzApplication->AccessToken
                ];

                return $class::$function($query, $params);

            }

        }
        else{
            error_log('MicrobizzInterface reached ...');
            return "MicrobizzInterface reached ...";
        }

    }

    public function negotiate(){

        $params = $this->getRequest()->params();
        
        $ID = isset($params['ID']) ? $params['ID'] : false;

        if (!$ID) {
            error_log('Id missing in negotiate URL');
            return $this->httpError(404);
        }

        $MicrobizzApplication = MicrobizzApplication::get_by_id($ID);

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

        $RedirectUrl = "/admin/microbizzapplications/JoelGrondrup-Microbizz-MicrobizzApplication/EditForm/field/JoelGrondrup-Microbizz-MicrobizzApplication/item/" . $ID . "/edit";

        header('Location: ' . $RedirectUrl);
        exit;

    }
    
}

