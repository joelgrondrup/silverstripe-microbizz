<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;

class MicrobizzEvent extends DataObject {
	
    private static $db = array (
        'MicrobizzApplication' => 'Text',
        'MicrobizzApplicationID' => 'Text',
        'ModCode' => 'Text',
        'Hook' => 'Text',
        'Contract' => 'Text',
        'POST' => 'Text',
        'Log' => 'Text',
        'Title' => 'Text'
    );
	
    private static $has_one = [
        'MicrobizzHook' => MicrobizzHook::class
    ];

    private static $summary_fields = array(
        'ModCode' => 'Type',
        'Hook' => 'Hook',
        'Created' => 'Created',
        'Contract' => 'Contract',
        'Title' => 'Title'
    );
	
    private static $default_sort = "Created DESC";
    
	function getCMSFields() {
		
        $fields = parent::getCMSFields();

        $fields->removeByName("MicrobizzHookID");

        $fields->addFieldToTab('Root.Main', TextField::create("Title"));
		
		$MicrobizzApplicationTextField = new TextField("MicrobizzApplication");
		$MicrobizzApplicationTextField->setReadonly(true);
        $fields->addFieldToTab('Root.Main', $MicrobizzApplicationTextField);
        
        $MicrobizzApplicationIDTextField = new TextField("MicrobizzApplicationID");
		$MicrobizzApplicationIDTextField->setReadonly(true);
        $fields->addFieldToTab('Root.Main', $MicrobizzApplicationIDTextField);

        $ModcodeTextField = new TextField("ModCode");
		$ModcodeTextField->setReadonly(true);
        $fields->addFieldToTab('Root.Main', $ModcodeTextField);
        
        $HookTextField = new TextField("Hook");
		$HookTextField->setReadonly(true);
        $fields->addFieldToTab('Root.Main', $HookTextField);

        $ContractField = new TextField("Contract");
		$ContractField->setReadonly(true);
        $fields->addFieldToTab('Root.Main', $ContractField);

        $PostField = new TextareaField("POST");
		$PostField->setReadonly(true);
        $PostField->setRows(10);
        $fields->addFieldToTab('Root.Data', $PostField);
        
        $LogField = new TextareaField("Log");
		$LogField->setReadonly(true);
        $LogField->setRows(10);
        $fields->addFieldToTab('Root.Log', $LogField);

		return $fields;
		
	}
    
}