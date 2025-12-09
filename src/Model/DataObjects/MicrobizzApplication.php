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
            'APIKey' => 'Text',
            'AccessToken' => 'Text',
            'UserName' => 'Text',
            'Password' => 'Varchar(255)'
        );

        private static $has_many = array (
            'MicrobizzHooks' => 'MicrobizzHook',
            'MicrobizzInterfaces' => 'MicrobizzInterface'
        );
        
        private static $summary_fields = array(
            'Title' => 'Title'
        );
        
        private static $default_sort = "Title ASC";
        
        function getCMSFields() {
            
            $fields = parent::getCMSFields();

            $fields->removeByName("Main");
            $fields->removeByName("MicrobizzHooks");
            $fields->removeByName("MicrobizzInterfaces");

            $fields->addFieldsToTab(
                'Root.Config',
                [
                    TextField::create('Title', 'Title'),
                    LiteralField::create("Developer", "<h2>Developer information</h2>"),
                    TextField::create('PublicKey', 'Public Key'),
                    TextField::create('SecretKey', 'Secret Key'),
                    LiteralField::create("AccessInformation", "<h2>Access information</h2>"),
                    TextField::create('AccessToken','Access token')->setReadonly(true),
                    TextField::create('EndPoint', 'EndPoint')->setReadonly(true),
                    LiteralField::create("Api", "<h2>API information</h2>"),
                    TextField::create('Contract','Contract','1234'),
                    TextField::create('UserName','Username','youremail@email.com'),
                    PasswordField::create('Password','Password','your-password')   
                ]
            );

            $config = GridFieldConfig_RecordEditor::create();

            $fields->addFieldToTab(
                'Root.Hooks',
                GridField::create('MicrobizzHooks', 'Microbizz webhooks', $this->MicrobizzHooks(), $config)
            );

            $fields->addFieldToTab(
                'Root.Interfaces',
                GridField::create('MicrobizzInterfaces', 'Microbizz interfaces', $this->MicrobizzInterfaces(), $config)
            );

            return $fields;
            
        }

        public function getCMSActions()
        {
            $actions = parent::getCMSActions();

            $activateHooksAndInterfacesLink = new CustomLink('activateWebhooksAndInterfaces','Activate hooks and interfaces');
            $activateHooksAndInterfacesLink->setButtonIcon(SilverStripeIcons::ICON_EXPORT);
            $activateHooksAndInterfacesLink->setNewWindow(false);

            $actions->push($activateHooksAndInterfacesLink);

            return $actions;
        }

        public function activateWebhooksAndInterfaces($request) {

            if (isset($_SERVER['HTTPS']) &&
                ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
                isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $Protocol = 'https://';
            }
            else {
                $Protocol = 'http://';
            }

            $MicrobizzLink = 'https://system.microbizz.dk/appconnect/';
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

            foreach ($this->MicrobizzInterfaces() as $MicrobizzInterface) {

                $Hook = [
                    'modcode' => $MicrobizzInterface->ModCode,
                    'hook' => $MicrobizzInterface->Hook,
                    'title' => $MicrobizzInterface->Title,
                    'url' => $Protocol . $_SERVER['SERVER_NAME'] . '/microbizz/interface/' . $this->ID . '/' . $MicrobizzInterface->ID
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
