1 config your  MCH_KEY, MCH_ID, LOGIN_NAME in CreditCardV3 class.

2 using following code sample to call Iotpay creditcard V3 api.


<?php
require_once('creditcardv3.php');

$returnurl = 'https://develop.iotpay.ca/new/v3dev/result.php?abc=111&code=234&cardid=12345678';
$notifyurl = 'https://develop.iotpay.ca/new/v3dev/notify.php';

$v3 = new CreditCardV3();

$v3->addCard('1234d5678',$returnurl);
$v3->queryCard('1234d5678');

//for recurring purchase
$v3->purchase('1234s5678','11113f1',0.01,$returnurl,$notifyurl);

//for receive notify
file_put_contents("./log.txt", json_encode($v3->receiveNotification()). "\r\n", FILE_APPEND);

?>
