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
First create a Microbizz Application in the CMS and insert a public and secret key (contact [Ventu](https://micropedia.microbizz.com/tec/making-a-microbizz-app) commercial support for these keys).

After this finish the configuration by filling out endpoint, contract, apikey, username and password. The endpoint is the url to the microbizz api endpoint. The contract is the contract number you use when you log into microbizz. Apikey, username and password can be created in your microbizz system.

### Hooks for Event Endpoints

To grab your first event objects from Microbizz you must create a Webhook and fill out a Mod and a Hook code. Remember that you can only use the module for Event endpoint types, interface events, app interface events and settings eventsare not (yet) supported by this module.

You can add as many hooks to event endpoints as you want. Available events can be found [here](https://micropedia.microbizz.com/tec/making-a-microbizz-app#MakingaMicrobizzapp-Programmingendpoints). After adding event hooks you can activate them by clicking on the button "Activate webhooks".

Once you receive data from Microbizz you can either use the "Event log" tab to see all data that is coming from Microbizz or you can go into each separate Hook to view events only for the specific webhook. 

## Working with Event data 

For every hook you can create your own functions that will be called when the hook is fired. All possible actions will automatically be listed in a dropdown under the "Actions" tab. To create a function you must create a class that extends the BaseWebhook class and add one or more static functions to it.

### Example

```php
<?php

class TodoWebhooks extends BaseWebhook {

    static function closeTodosWhenControlled($data, $params, $event) {

        //Do stuff

    }

}
```