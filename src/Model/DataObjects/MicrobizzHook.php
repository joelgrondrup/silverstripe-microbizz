<?php

namespace {

    use SilverStripe\Forms\DropdownField;
    use SilverStripe\ORM\ArrayList;
    use SilverStripe\ORM\DataObject;
    use SilverStripe\Admin\ModelAdmin;
    use SilverStripe\Forms\TextareaField;
    use SilverStripe\View\ArrayData;

    class MicrobizzHook extends DataObject {
        
        private static $db = array (
            'Title' => 'Varchar(255)',
            'ModCode' => 'Varchar(255)',
            'Hook' => 'Varchar(255)',
            'Handle' => 'Text'
        );

        private static $has_one = [
            'MicrobizzApplication' => MicrobizzApplication::class
        ];

        private static $has_many = [
            'MicrobizzEvents' => MicrobizzEvent::class
        ];
        
        private static $summary_fields = array(
            'Title' => 'Title',
            'ModCode' => 'ModCode',
            'Hook' => 'Hook'
        );
        
        private static $default_sort = "Title ASC";
        
        function getCMSFields() {
            
            $fields = parent::getCMSFields();

            $fields->removeByName("MicrobizzApplicationID");

            $actionsList = ArrayList::create();

            $action = ArrayData::create();
            $action->ID = "";
            $action->Title = "Choose an action";
            $actionsList->add($action);

            $directoryToSearch = $_SERVER["DOCUMENT_ROOT"] . "/../app/src/";  
            $classes = BaseWebhook::searchClassesInFiles($directoryToSearch);

            foreach ($classes as $classInfo) {
                
                //echo "Class '{$classInfo['class']}' found in {$classInfo['file']}\n";
                if (is_subclass_of($classInfo['class'], "BaseWebhook")) {
                    
                    $reflectionClass = new ReflectionClass($classInfo['class']);
                    $methods = $reflectionClass->getMethods(ReflectionMethod::IS_STATIC);
                    
                    foreach ($methods as $method) {
                        
                        if ($method->getName() != 'searchClassesInFiles'){

                            $action = ArrayData::create();
                            $action->ID = $classInfo['class'] . "::" . $method->getName();
                            $action->Title = $classInfo['class'] . "::" . $method->getName();
                            $actionsList->add($action);

                        }

                    }

                }
            }
                    
            $fields->addFieldToTab("Root.Action", DropdownField::create("Handle", "Choose action", $actionsList->map()));

            return $fields;
            
        }
        
    }

}