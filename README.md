
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

## Payment or refund or void success

retCode == 'SUCCESS' && (status == 2 or status == 3)

## Simple purchase

1, Call purchase and redirect to redirectUrl to let user input credit card info;

2, After purchase, will redirect to returnUrl;

3, If the transaction is successful, IOTPay will notify to notifyUrl;

```php
require_once('creditcardv3.php');

$returnurl = 'https://develop.iotpay.ca/new/v3dev/result.php?abc=111&code=234&cardid=12345678';
$notifyurl = 'https://develop.iotpay.ca/new/v3dev/notify.php';

$mchorderno = '11113f1';
$amount     = 0.01;
$v3         = new CreditCardV3();
$channel  = 'PF_CC';//channel could be 'PF_CC' or  'UPI_EX'
                      //'UPI_EX' is only for union card 
$res = $v3->purchase($mchorderno,$amount,$returnurl,$notifyurl,$channel); 
if ($res['retCode'] == 'SUCCESS') {
	header('Location: ' . $res['retData']['redirectUrl']);//Redirect to Iotpay credit card input page 
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
$channel  = 'PF_CC';//channel could be 'PF_CC' or  'UPI_EX'
                      //'UPI_EX' is only for union card 
$res = $v3->addCard('12345678',$returnurl,$channel);//redirect to card input page
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
$notifyurl  = 'https://yournotifyurl'; // 'UPI_EX' must include notifyurl
$ret = $v3->withToken($cardid,$mchorderno,$amount,$notifyurl);
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

3 Use SecureId as parameters and call JS function Iotpay(secureid,addorpurchase，option).
addorpurchase must be Add or Purchase.

```html
<script type="text/javascript" src="https://ccapi.iotpaycloud.com/cc/iotpaycc.js"></script>
 <div id="iotpay_normal"></div>
<script>
    let callback = function(event) {
	console.log(event);
	if (event.result == 'SUCCESS') {
	    // Add/Pay Success
	    // Return data will be contained in event.detail
	    // if (event.detail.retData && event.detail.retData.redirectUrl) {
	    //     window.location.replace(event.detail.retData.redirectUrl);
	    // }
	} else if (event.result == 'FAIL' && event.message == 'Timeout') {
	    // Union Pay require query orders. We will be timed out after 30 tries and merchant needs to query the order.
	    //
	} else if (event.result == 'FAIL') {
	    // Add/Pay Failed
	    // Return data will be contained in event.detail
	    // if (event.detail.retData && event.detail.retData.redirectUrl) {
	    //     window.location.replace(event.detail.retData.redirectUrl);
	    // }
	}
	if (event.detail.retData && event.detail.retData.redirectUrl) {
	    window.location.replace(event.detail.retData.redirectUrl);
	}
    }
    let secureId ='3adfd*******3fdfd'//get secureId from addCard or Purchase endpoint.
    let iotpay_normal = Iotpay(secureId, 'Add');// second params must be Add or Pay
    iotpay_normal.mount('#iotpay_normal', callback);
</script>
```
We provide several credit card input css style. Please check following static Html:

card input page with css option:

https://develop.iotpay.ca/newdemo/card/v3/mchpagewithoption.php

https://develop.iotpay.ca/newdemo/card/v3/mchpagewithoption1.php

https://develop.iotpay.ca/newdemo/card/v3/mchpagewithoption2.php

https://develop.iotpay.ca/newdemo/card/v3/mchpagewithoption3.php

card input page without css option:

https://develop.iotpay.ca/newdemo/card/v3/mchpagewithoption.php

## Contributing


Iotpay team MT.
