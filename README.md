# AlrajhiBank
## Installation

You can install the package via [Composer](https://getcomposer.org).

```bash
composer require maree/alrajhibank-payments
```
Publish your alrajhibank config file with

```bash
php artisan vendor:publish --provider="maree\alrajhibankPayments\AlrajhibankServiceProvider" --tag="alrajhiBank"
```
then change your AlrajhiBank config from config/alrajhiBank.php file
```php
    "id"             => "" ,
    "password"       => "" ,
    "currencyCode"   => "682",//SAR => 682
    "encryption_key" =>  "",
```
## Usage

## first step
```php
use maree\alrajhibankPayments\AlrajhiBank;
$response = AlrajhiBank::checkout($amount = 0.0,$responseURL='',$errorURL='');  

```
## note 
- this function return ['key' => 'success' ,'checkoutId' => $payment_id , 'responseData' => $responseData] //key = success or fail
- use checkoutId to save transaction in database
- use checkoutId in view page in next step

## second step
- return view page with $checkoutId to show payment proccess
```php
<iframe src="{{config('alrajhiBank.view_url').$checkoutId}}" style="width: 100%; height: 100%" title="description"></iframe>

```
## note 
- create route for response url 'show-response-route' 
EX: Route::get('show-response-route', 'PaymentsController@paymentresponse')->name('show-response-route'); 
- create route for error response url 'show-response-error' 
EX: Route::get('show-response-error', 'PaymentsController@paymentresponseError')->name('show-response-error'); 
- create function for checkout response 'paymentresponse'
- use that function to check if payment failed or success

## inside 'paymentresponse' and 'paymentresponseError' functions use:
```php
use maree\alrajhibankPayments\AlrajhiBank;

//trandata = $request->trandata
$response = AlrajhiBank::checkoutResponseStatus($trandata);  

```
return response like: 
```php

['key' => 'success' , 'responseData' => $responseData]; 

```
or 

```php

 ['key' => 'fail', 'responseData' => $responseData];

```
note: you can use response from data to save transactions in database 










