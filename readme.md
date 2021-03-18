# Tucuota php client

* [Install](#install)
* [Setup](#setup)
* [Examples](#examples)

<a name="install"></a>
## Install

Via Composer

``` bash
$ composer config repositories.repo git git@github.com:tucuota/tucuota-php.git
$ composer require tucuota/tucuota-php
```

<a name="setup"></a>
## Setup

### Configure your credentials

* Get your **API_TOKEN** in the following address:
    * Production: https://www.tucuota.com/dashboard/developers
    * Sandbox: https://sandbox.tucuota.com/dashboard/developers

```php
use TuCuota\TuCuota;

// Use in sandbox (test mode)
$client = new TuCuota("REPLACE_THIS_FOR_YOUR_SANDBOX_API_TOKEN", "sandbox");

// Use in production
$client = new TuCuota("REPLACE_THIS_FOR_YOUR_API_TOKEN");
```



<a name="examples"></a>
## Examples

### Customer

#### Create customer

```php
$request = $client->post('customers', [
    "email" => "ejemplo@gmail.com",
    "name" => "Juan Tile",
    "metadata" => [
        'external_id' => 51,
    ],
]);

var_dump($request);
```

#### Get customers
```php
$request = $client->get('customers');

var_dump($request);
```

#### Get customer
```php
$request = $client->get('customers/CSvQwEV56Dp2');

var_dump($request);
```



### Payments

#### Create payment

```php
$request = $client->post('payments', [
    "description" => "Pago único",
    "customer_id" => "CSvQwEV56Dp2",
    "payment_method_number" => "4024007127322104",
    "amount" => 12000,
]);

var_dump($request);
```

#### Get payments
```php
$request = $client->get('payments');

var_dump($request);
```

#### Get payment
```php
$request = $client->get('payments/PYvQwEV56Dp2');

var_dump($request);
```


## Security

If you discover any security related issues, please email juandelperal@tucuota.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.