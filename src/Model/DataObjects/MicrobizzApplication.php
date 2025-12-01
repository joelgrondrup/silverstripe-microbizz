<?php

namespace {
    
    use LeKoala\CmsActions\CustomLink;
    use LeKoala\CmsActions\SilverStripeIcons;
    use SilverStripe\Forms\LiteralField;
    use SilverStripe\Forms\PasswordField;
    use SilverStripe\ORM\DataObject;
    use SilverStripe\Admin\ModelAdmin;
    use SilverStripe\Forms\GridField\GridField;
    use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
    use SilverStripe\Forms\TextField;

    class MicrobizzApplicationModelAdmin extends ModelAdmin 
    {

        private static $managed_models = [
            'MicrobizzApplication'
        ];

        private static $url_segment = 'microbizzapplications';

        private static $menu_title = 'Microbizz applications';
        
    }

    class MicrobizzApplication extends DataObject {
        
        private static $db = array (
            'Title' => 'Varchar(255)',
            'PublicKey' => 'Text',
            'SecretKey' => 'Text',
            'EndPoint' => 'Text',
            'Contract' => 'Text',
            'AccessToken' => 'Text',
            'UserName' => 'Text',
            'Password' => 'Varchar(255)'
        );

        private static $has_many = array (
            'MicrobizzHooks' => 'MicrobizzHook'
        );
        
        private static $summary_fields = array(
            'Title' => 'Title'
        );
        
        private static $default_sort = "Title ASC";
        
        function getCMSFields() {
            
            $fields = parent::getCMSFields();

            $fields->removeByName("Main");
            $fields->removeByName("MicrobizzHooks");

            $fields->addFieldsToTab(
                'Root.Config',
                [
                    TextField::create('Title', 'Title'),
                    LiteralField::create("Developer", "<h2>Developer information</h2>"),
                    TextField::create('PublicKey', 'Public Key'),
                    TextField::create('SecretKey', 'Secret Key'),
                    LiteralField::create("Api", "<h2>API information</h2>"),
                    TextField::create('EndPoint', 'EndPoint', 'https://system.microbizz.dk/api/endpoint.php'),
                    TextField::create('Contract','Contract','1234'),
                    TextField::create('AccessToken','API key','1234-1234-1234-1234-1234-1234-1234'),
                    LiteralField::create("Api", "<h2>User information</h2>"),
                    TextField::create('UserName','Username','youremail@email.com'),
                    PasswordField::create('Password','Password','your-password')   
                ]
            );

            $config = GridFieldConfig_RecordEditor::create();

            $fields->addFieldToTab(
                'Root.Hooks',
                $hooksGridField = GridField::create('MicrobizzHooks', 'Microbizz webhooks', $this->MicrobizzHooks(), $config)
            );

            return $fields;
            
        }

        public function getCMSActions()
        {
            $actions = parent::getCMSActions();

            $activateWebhooksLink = new CustomLink('activateWebhooks','Activate webhooks');
            $activateWebhooksLink->setButtonIcon(SilverStripeIcons::ICON_EXPORT);
            $activateWebhooksLink->setNewWindow(true);

            $actions->push($activateWebhooksLink);

            return $actions;
        }

        public function activateWebhooks($request) {

            if (isset($_SERVER['HTTPS']) &&
                ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
                isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $Protocol = 'https://';
            }
            else {
                $Protocol = 'http://';
            }

            $MicrobizzLink = isset($this->EndPoint) ?? 'https://system.microbizz.dk/appconnect/';
            $PublicKey = $this->PublicKey;
            $NegotiateURL = $Protocol . $_SERVER['SERVER_NAME'] . "/microbizz/negotiate/" . $this->ID;
            $ReturnURL = $Protocol . $_SERVER['SERVER_NAME'] . "/microbizz/returnurl/" . $this->ID;

            $Hooks = [];

            foreach ($this->MicrobizzHooks() as $MicrobizzHook) {

                $Hook = [
                    'modcode' => $MicrobizzHook->ModCode,
                    'hook' => $MicrobizzHook->Hook,
                    'title' => $MicrobizzHook->Title,
                    'url' => $Protocol . $_SERVER['SERVER_NAME'] . '/microbizz/webhook/' . $this->ID . '/' . $MicrobizzHook->ID
                ];

                array_push($Hooks, $Hook);
            }

            $RequestData = [
                'publicid' => $PublicKey,
                'negotiateurl' => $NegotiateURL,
                'returnurl' => $ReturnURL,
                'hooks' => $Hooks
            ];

            $RedirectUrl = $MicrobizzLink . '?request=' . json_encode($RequestData);

            header('Location: ' . $RedirectUrl);
            exit;

        }
        
    }

}
