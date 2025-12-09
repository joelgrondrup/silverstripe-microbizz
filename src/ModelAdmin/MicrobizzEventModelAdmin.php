<?php

use SilverStripe\Admin\ModelAdmin;
use Colymba\BulkManager\BulkManager;

class MicrobizzEventsModelAdmin extends ModelAdmin 
{

    private static $managed_models = [
        'MicrobizzEvent'
    ];

    private static $url_segment = 'eventlogs';

    private static $menu_title = 'Event logs';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $grid = $form->Fields()->fieldByName($this->sanitiseClassName(MicrobizzEvent::class));

        $config = $grid->getConfig();
        $config->addComponent(new BulkManager());

        return $form;
    }
    
}