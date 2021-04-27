
## Installation

Download creditcardv3.php and included in your project

# Before Integration

1 Config your  MCH_KEY, MCH_ID, LOGIN_NAME in CreditCardV3 class.

2 Using following code sample to call Iotpay creditcard V3 api.

# Choose Integration Modes and Redirect Methods

1 Integration Modes

Simple purchase: The customers input card info and purchase once. The customers will be prompted to input card info each time when they purchase.

Recurring purchase: The customers input card info once, can purchase with the tokenized card multiple times.

2 Redirect Methods

Redirect methods: The customers will be redirected to Iotpay webpage to input credit card info.

Securefield methods: The customers will be redirected to merchant webpage which includes Iotpay iframe to input credit card info.

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
$res = $v3->purchase($cardid,$mchorderno,$amount,$returnurl,$notifyurl); 
if ($res['retCode'] == 'SUCCESS') {
	header('Location: ' . $res['retData']['redirectUrl']);//Redirect to card input page 
} else {
	echo $res['retMsg'];
}
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
$res = $v3->addCard('12345678',$returnurl);//redirect to card input page
if ($res['retCode'] == 'SUCCESS') {
	header('Location: ' . $res['retData']['redirectUrl']);//Redirect to Iotpay credit card input page
} else {
	echo $res['retMsg'];
}
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

$cardid     = '12345678';
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
## About Securefield

A Iframe will be embedded in merchant web page which allow your customers to input creditcard info. 

1 Include following code in your web page.

2 Get SecureId from addCard or Purchase API.

3 Use SecureId as parameters and call JS function initIotpaySecurePay(secureid,addorpurchase).
addorpurchase must be Add or Purchase.

```html
<script type="text/javascript" src="https://ccapi.iotpaycloud.com/iotpaycc.js"></script>
<div id="iotpay_creditcard"/>
<script>
  var secureid = 'addfd2***2323sdf'   // get secureid from addCard or Purchase API
  initIotpaySecurePay(secureid,'Add');// second params must be Add or Purchase
</script>
```

## Contributing

Iotpay team MT.
