
## Installation

Download creditcardv3.php and included in your project

# Before Integration

1 Config your  MCH_KEY, MCH_ID, LOGIN_NAME in CreditCardV3 class.

2 Using following code sample to call Iotpay creditcard V3 api.


## Simple purchase

1, Call purchase and redirect to redirectUrl to let user input credit card info;

2, After purchase, will redirect to returnUrl;

3, If the transaction is successful, IOTPay will notify to notifyUrl;

```php
require_once('creditcardv3.php');

$returnurl = 'https://develop.iotpay.ca/new/v3dev/result.php?abc=111&code=234&cardid=12345678';
$notifyurl = 'https://develop.iotpay.ca/new/v3dev/notify.php';

$cardid     = '1234s5678';
$mchorderno = '11113f1';
$amount     = 0.01;
$v3         = new CreditCardV3();
$v3->purchase($cardid,$mchorderno,$amount,$returnurl,$notifyurl); //redirect to card input page
```
## Simple purchase--handling notification

```php
require_once('creditcardv3.php');

$v3  = new CreditCardV3();
$ret = $v3->receiveNotification();
if($ret['retCode'] == 'SUCCESS'){
   $order = $ret['retData'];
   if($order['status'] ===2 || $order['status'] ===3 ){
      //payment success
   }
}

```
## Recurring purchase

1, Call addcard and then redirect to retData.redirectUrl to let user input credit card info;

2, After addcard, will redirect to returnUrl with the following parameters:

     If success: retCode=SUCCESS
     If fail: retCode=FAIL&retMsg=xxxx
   
3, Querycard to get cardinfo information

4, If addcard is successful, call purchasewithtoken to do real purchase

```php
//add card
require_once('creditcardv3.php');
$returnurl = 'https://develop.iotpay.ca/new/v3dev/result.php?abc=111&code=234&cardid=12345678';
$v3 = new CreditCardV3();
$v3->addCard('1234d5678',$returnurl);//redirect to card input page
```

```php
//query card
require_once('creditcardv3.php');
$v3 = new CreditCardV3();
$cardinfo = $v3->queryCard('1234d5678'));
```

```php
//recurring puschase
require_once('creditcardv3.php');
$v3 = new CreditCardV3();
$cardinfo = $v3->queryCard('1234d5678'));

$cardid     = '1234s5678';
$mchorderno = '11113f1';
$amount     = 0.01;
$ret = $v3->withToken($cardid,$mchorderno,$amount);
if($ret['retCode'] == 'SUCCESS'){
   $order = $ret['retData'];
   if($order['status'] ===2 || $order['status'] ===3 ){
      //payment success
   }
}
```

## Contributing

Iotpay team.
