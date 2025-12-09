## Overview

This module provides an interface to listen to one or more [Microbizz systems](https://microbizz.com/en), catch [complex objects](https://micropedia.microbizz.com/tec/complex-types) when commands are fired and act upon them.

## Installation

```sh
composer require joelgrondrup/silverstripe-microbizz
```
After this run:
```sh
dev/build
```
## Configuration

After building the database you should have a button in the CMS called "Microbizz Applications".
First create a Microbizz Application in the CMS by inserting a public and secret key (contact [Ventu](https://micropedia.microbizz.com/tec/making-a-microbizz-app) commercial support for these keys).

### Activate application
After this you must make a handshake with Microbizz to get an access token, contract id and endpoint url. To do this just click on "Activate" and follow the instructions. (If you want to you can fill out hooks and interfaces before activating, but you can also activate them afterwards.)

If everything went well you should see the fields with access token, endpoint and contract filled out.

### Enter user information for api requests
After this, finish the configuration by filling out an apikey, username and password created in the matching Microbizz-system. This can be useful later when you make extensions to hooks and interfaces.

## Hooks for Event Endpoints
To grab your first event objects from Microbizz you must create a webhook and fill out a mod and a hook code. Remember that the hooks are used for getting event endpoint types.

You can add as many hooks as you want to. Available events can be found [here](https://micropedia.microbizz.com/tec/making-a-microbizz-app#MakingaMicrobizzapp-Programmingendpoints). After adding event hooks you can activate them by clicking on the button "Activate" and go through the form again at Microbizz. 

Once you receive data from Microbizz you can either use the "Event log" tab to see all data that is coming from Microbizz or you can go into each separate hook to view events only for the specific webhook. 

### Working with Event data 

For every hook you can create your own functions that will be called when the hook is fired. All possible actions will automatically be listed in a dropdown under the "Actions" tab. To create a function you must create a class that extends the BaseWebhook class and add one or more static functions to it.

### Example

```php
<?php

class TodoWebhooks extends BaseWebhook {

    static function closeTodosWhenControlled($data, $params, $event) {

        //Do stuff

        $event->Log .= "Closed todo with id {$data['id']}";
        $event->write();

    }

}
```

Any extension of the BaseWebhook should receive three parameters: data, params and event. Data is the data that is sent from Microbizz (i.e. a customer or todo object), params is an array with the parameters from the Microbizz application and the event is the MicrobizzEvent object in SilverStripe which you can use to add information to the log in the CMS. This is especially useful for debugging and for explaining what happened.

## Interface Endpoints

Interface endpoints are iframes that are placed into the Microbizz CRM-system in different places. They are named "interface" in the [documentation](https://micropedia.microbizz.com/tec/making-a-microbizz-app#MakingaMicrobizzapp-Programmingendpoints). To create an interface endpoint, you need to create a class that extends the BaseInterfaceEndpoint class. This class should have a static function that receives three parameters: data and params.

The data is the GET parameters from the iframe and params is an array with the parameters from the Microbizz application. The params variable also holds the sessiontokenresult, interface and application objects.

### Example

```php
<?php

class TodoTabInterface extends BaseInterface {

    static function showMyCustomTabInterface($data, $params) {

        return $this->renderWith("MyCustomTabInterface");

    }

}
```

## License

This module is licensed under the MIT license. 