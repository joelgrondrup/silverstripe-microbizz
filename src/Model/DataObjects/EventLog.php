<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;

class EventLogModelAdmin extends ModelAdmin 
{

    private static $managed_models = [
        'MicrobizzEvent',
        'EventLog'
    ];

    private static $url_segment = 'eventlogs';

    private static $menu_title = 'Event logs';
    
}

class EventLog extends DataObject {
	
    private static $db = array (
        'Title' => 'Text',
        'Log' => 'Text'
    );
	
    private static $summary_fields = array(
        'Title' => 'Title',
        'Created' => 'Created'
    );
	
    private static $default_sort = "Created DESC";
    
	function getCMSFields() {
		
        $fields = parent::getCMSFields();
		
		$fields->removeByName("PageID");
		
        $fields->addFieldToTab('Root.Main', new TextField("Title"));
        $fields->addFieldToTab('Root.Main', new TextareaField("Log"));
        
		return $fields;
		
	}
    
}