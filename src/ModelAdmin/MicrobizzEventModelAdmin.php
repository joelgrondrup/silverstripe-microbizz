<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;

class MicrobizzEventsModelAdmin extends ModelAdmin 
{

    private static $managed_models = [
        'MicrobizzEvent'
    ];

    private static $url_segment = 'eventlogs';

    private static $menu_title = 'Event logs';
    
}