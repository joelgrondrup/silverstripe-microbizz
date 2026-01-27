<?php

class MicrobizzHelper {

    /**
     * Generates the Microbizz activation URL.
     * * @param string|null $overrideReturnUrl Optional custom return URL
     * @return string
     */
    public static function generateActivationUrl($microbizzApplication, $overrideReturnUrl = null) {
        
        $isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)) ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
        
        $protocol = $isHttps ? 'https://' : 'http://';
        $baseUrl = $protocol . $_SERVER['SERVER_NAME'];

        $negotiateURL = "$baseUrl/microbizz/negotiate/{$microbizzApplication->ID}";
        
        // Use override if provided, otherwise use default
        $returnURL = $overrideReturnUrl ?? "$baseUrl/microbizz/returnurl/{$microbizzApplication->ID}";

        // 3. Collect Hooks and Interfaces
        $hooks = [];
        $sources = [
            'webhook' => $microbizzApplication->MicrobizzHooks(),
            'interface' => $microbizzApplication->MicrobizzInterfaces()
        ];

        foreach ($sources as $type => $collection) {
            foreach ($collection as $item) {
                $hooks[] = [
                    'modcode' => $item->ModCode,
                    'hook'    => $item->Hook,
                    'title'   => $item->Title,
                    'url'     => "$baseUrl/microbizz/$type/{$microbizzApplication->ID}/{$item->ID}"
                ];
            }
        }

        $requestData = [
            'publicid'     => $microbizzApplication->PublicKey,
            'negotiateurl' => $negotiateURL,
            'returnurl'    => $returnURL,
            'hooks'        => $hooks
        ];

        return 'https://system.microbizz.dk/appconnect/?request=' . json_encode($requestData);
    }

}