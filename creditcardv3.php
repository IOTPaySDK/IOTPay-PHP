<?php

class CreditCardV3 {
 
    const MCH_KEY     = '**************';
    const MCH_ID      = '1*******6';
    const LOGIN_NAME  = '******';

    const API_ADDCARD    = 'https://ccapi.iotpaycloud.com/v3/cc_addcard';
    const API_QUERYCARD  = 'https://ccapi.iotpaycloud.com/v3/cc_querycard';

    const API_PURCHASE   = 'https://ccapi.iotpaycloud.com/v3/cc_purchase';   
    const API_WITHTOKEN  = 'https://ccapi.iotpaycloud.com/v3/cc_purchasewithtoken';
    const API_WITHWALLET = 'https://ccapi.iotpaycloud.com/v3/cc_purchasewithwallet';
    
    const API_QUERYORDER = 'https://ccapi.iotpaycloud.com/v3/cc_query';

    const API_VOID     = 'https://ccapi.iotpaycloud.com/v3/cc_void';
    const API_REFUND   = 'https://ccapi.iotpaycloud.com/v3/cc_refund';

    public function __construct()
    {
    }
    private function request($url, $params)
    {
        $ch = curl_init();
        $this_header = array("content-type:application/json;charset=UTF-8");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this_header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /** Signature string
     *$prestr: Strings to be signed
     *return: Signature result
     */
    private function  md5sign($prestr, $sign_type)
    {
        $sign = '';
        if ($sign_type == 'MD5') {
            $sign = strtoupper(md5($prestr));//All uppercase letters
        } else {
            die("The signature method " . $sign_type . " is not supported at this time");
        }
        return $sign;
    }

    /**
     *Put all the elements of the array into a string with the "&" character according to the pattern of "parameter = parameter value"
     *$array: array to be stitched
     *return: string after stitching completed
     */
    private function  create_linkstring($array)
    {
        $arg = "";
        foreach ($array as $k => $v) {
            if ($v !== '') {
                $arg .= $k . '=' . $v . '&';
            }
        }
        $arg = substr($arg, 0, strlen($arg) - 1);             //Remove the last & character
        return $arg;
    }

    private function  build_mysign($sort_array, $key, $sign_type = "MD5")
    {
        $prestr = $this->create_linkstring($sort_array);
        $prestr = $prestr . "&key=" . $key;
        //Connect the stitched string directly to the security check code
        $mysgin = $this->md5sign($prestr, $sign_type);
        //Sign the final string to get the signature result
        return $mysgin;
    }

    /**Sort array
     *$array: array before sorting
     *return: sorted array
     */
    private function arg_sort($array)
    {
        ksort($array, SORT_NATURAL | SORT_FLAG_CASE);
        reset($array);
        return $array;
    }
    public   function addCard($cardid,$returnurl)
    {
        $arr = array(
            'cardId' => $cardid,
            'mchId'  => self::MCH_ID,
            'returnUrl' => $returnurl,
            'loginName' => self::LOGIN_NAME,
        );
        $sort_array  = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array,self::MCH_KEY, "MD5");
        $param   = json_encode($arr);
        $resBody = $this->request(self::API_ADDCARD, $param);
        //echo $resBody;
        $res = json_decode($resBody, true);
	return  $res;
        /*if ($res['retCode'] == 'SUCCESS') {
            header('Location: ' . $res['retData']['redirectUrl']);//Redirect to addcard page
        } else {
            echo $res['retMsg'];
        }*/
    }
    public function queryCard($cardid)
    {
        $arr = array(
            'cardId' => $cardid,
            'mchId'  => self::MCH_ID,           
        );
        $sort_array  = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array,self::MCH_KEY, "MD5");
        $param   = json_encode($arr);
        $resBody = $this->request(self::API_QUERYCARD, $param);
        //echo $resBody;
        return json_decode($resBody, true);
    }
    public function purchase($cardid,$mchorderno,$amount,$returnurl,$notifyurl)
    {
        $arr = array(
            'cardId'     => $cardid,
            'mchOrderNo' => $mchorderno,
            'mchId'      => self::MCH_ID,
            'currency'   => 'CAD',
            'amount'     => intval($amount * 100),//change to cents
            'loginName'  => self::LOGIN_NAME,
            'notifyUrl'  => $notifyurl,
            'returnUrl'  => $returnurl,
        );
        $sort_array  = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");
        $param       = json_encode($arr);
        $resBody     = $this->request(self::API_PURCHASE, $param);//Submit to the gateway
        //echo $resBody;
        $res = json_decode($resBody, true);
	return  $res;
/*
        if ($res['retCode'] == 'SUCCESS') {
            header('Location: ' . $res['retData']['redirectUrl']);//Redirect to payment page 
        } else {
            echo $res['retMsg'];
        }*/
    }

    public   function withToken($cardid, $mchorderno, $amount)//purchase with token
    {
        $arr = array(
            'mchId'      => self::MCH_ID,
            'mchOrderNo' => $mchorderno,
            'cardId'     => $cardid,
            'amount'     => intval($amount * 100),
            'currency'   => 'CAD',
            'loginName'  => self::LOGIN_NAME,
        );
        $sort_array  = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");
        $param       = json_encode($arr);
        $resBody     = $this->request(self::API_WITHTOKEN, $param);//Submit to the gateway
      //echo  $resBody;
        return json_decode($resBody, true);
    }
    public   function withWallet($cardid, $mchorderno, $amount,$walletdata,$wallettype)
    {
        $arr = array(
            'cardId'     => $cardid,
            'mchOrderNo' => $mchorderno,
            'mchId'      => self::MCH_ID,
            'currency'   => 'CAD',
            'amount'     => intval($amount * 100),
            'loginName'  => self::LOGIN_NAME, 
            'walletType' => $wallettype,
            'walletData' => $walletdata,
        );
        $sort_array  = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");
        $param       = json_encode($arr);
        $resBody     = $this->request(self::API_WITHWALLET, $param);//Submit to the gateway
        //echo $resBody;
        return json_decode($resBody, true);
    }
    public   function queryOrder($payorderid,$mchorderno)
    {
        $arr['mchId'] = self::MCH_ID;
        if($payorderid != '' && $payorderid != ' ')
        {
            $arr['payOrderId'] = $payorderid;
        }elseif($mchorderno != '' && $mchorderno != ' ')
        {
            $arr['mchOrderNo'] = $mchorderno;
        }else{
            return [];
        }
        $sort_array  = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");//Generate signature parameter sign
        $param       = json_encode($arr);
        $resBody     = $this->request(self::API_QUERYORDER, $param);//Submit to the gateway
        return json_decode($resBody, true);
    }
    public function void($originalorderid,$mchrefundno)
    {
        $arr = array(
            'mchId'       => self::MCH_ID,
            'mchRefundNo' => $mchrefundno,
            'loginName'   => self::LOGIN_NAME,
            'payOrderId'  => $originalorderid,
        );
        $sort_array = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");//Generate signature parameter sign
        $param = json_encode($arr);
        $resBody = $this->request(self::API_VOID, $param);//Submit to the gateway
        //echo $resBody;
        return json_decode($resBody, true);       
    }
    public   function refund($originalorderid,$mchrefundno,$amount)
    {
        $arr = array(
            'mchId'       => self::MCH_ID,
            'mchRefundNo' => $mchrefundno,
            'loginName'   => self::LOGIN_NAME,
            'payOrderId'  => $originalorderid,
            'refundAmount'     => intval($amount * 100),
        );
        $sort_array = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");//Generate signature parameter sign
        $param = json_encode($arr);
        $resBody = $this->request(self::API_REFUND, $param);//Submit to the gateway
        //echo $resBody;
        return json_decode($resBody, true);      
    }
    public function receiveNotification()
    {
        $arr = json_decode(file_get_contents("php://input"),true);
        $backsign = $arr['sign'];
        unset($arr["sign"]);
        $sort_array = $this->arg_sort($arr);
        $mysign     = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");
        if ($mysign == $backsign) {           
            echo "SUCCESS";//DO NOT DELETE
            $retarr['retCode'] = 'SUCCESS';
            $retarr['retData'] = $arr;
            return $retarr;
        }else {
            echo "FAIL";//DO NOT DELETE
            $retarr['retCode'] = 'FAIL';
            $retarr['retData'] = $arr;
            return $retarr;
        }
    }
}
	
