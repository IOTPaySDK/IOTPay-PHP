# Before Integration

1 config your  MCH_KEY, MCH_ID, LOGIN_NAME in CreditCardV3 class.

2 using following code sample to call Iotpay creditcard V3 api.


## Installation

Download creditcardv3.php and included in your project

## Usage

```php
require_once('creditcardv3.php');

$returnurl = 'https://develop.iotpay.ca/new/v3dev/result.php?abc=111&code=234&cardid=12345678';
$notifyurl = 'https://develop.iotpay.ca/new/v3dev/notify.php';

$v3 = new CreditCardV3();
// add card
$v3->addCard('1234d5678',$returnurl);

//query card
$v3->queryCard('1234d5678'));

//recurring puschase
$v3->purchase('1234s5678','11113f1',0.01,$returnurl,$notifyurl);

//for receive notify
$v3->receiveNotification();


```

## Contributing

Iotpay team.
