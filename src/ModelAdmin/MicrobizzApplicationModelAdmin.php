<?php

namespace JoelGrondrup\Microbizz;

use SilverStripe\Admin\ModelAdmin;
use JoelGrondrup\Microbizz\MicrobizzApplication;

class MicrobizzApplicationModelAdmin extends ModelAdmin 
{

    private static $managed_models = [
        MicrobizzApplication::class,
    ];

    private static $url_segment = 'microbizzapplications';

    private static $menu_title = 'Microbizz applications';
    
}