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

After this finish the configuration by filling out endpoint, contract, apikey, username and password. The endpoint is the url to the microbizz api endpoint. The contract is the contract number you use when you log into microbizz. 

### Hooks for Event Endpoints

To create your first hook you must create a Webhook and fill out a Mod code and a Hook code. Remember that you can only use this interface for Event endpoint types, so interface, app interface and settings are not (yet) supported by this module.

You can add as many hooks to event endpoints as you want. Available events can be found [here](https://micropedia.microbizz.com/tec/making-a-microbizz-app#MakingaMicrobizzapp-Programmingendpoints). After adding event hooks you can activate them by clicking on the button "Activate webhooks".

Once you receive data from Microbizz you can use the "Event log" tab to see the data that is send to your webhook. 

## Working with Event data 

