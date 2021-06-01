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
	
    public $res = array('retCode' => 'FAIL','retMsg' => 'return null','retData' => '');

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
        $mysgin = $this->md5sign($prestr, $sign_type);
        return $mysgin;
    }

    private function arg_sort($array)
    {
        ksort($array, SORT_NATURAL | SORT_FLAG_CASE);
        reset($array);
        return $array;
    }
	
    public function addCard($cardid,$returnurl,$channelid)
    {
        $arr = array(
            'cardId' => $cardid,
            'mchId'  => self::MCH_ID,
            'returnUrl' => $returnurl,
            'loginName' => self::LOGIN_NAME,
	    'channelId' => $channelid,
        );
        $sort_array  = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array,self::MCH_KEY, "MD5");
        $param   = json_encode($arr);
        $resBody = $this->request(self::API_ADDCARD, $param);	
	if($resBody != '' || $resBody !=' '){
		$this->res = json_decode($resBody, true);
	}
	return  $this->res;
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
	if($resBody != '' || $resBody !=' '){
		$this->res = json_decode($resBody, true);
	}
	return $this->res;	
    }
	
    public function purchase($mchorderno,$amount,$returnurl,$notifyurl,$channelid)
    {
        $arr = array(
            'mchOrderNo' => $mchorderno,
            'mchId'      => self::MCH_ID,
            'currency'   => 'CAD',
            'amount'     => intval($amount * 100),//change to cents
            'loginName'  => self::LOGIN_NAME,
            'notifyUrl'  => $notifyurl,
            'returnUrl'  => $returnurl,
	    'channelId'  => $channelid,
        );
        $sort_array  = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");
        $param       = json_encode($arr);
        $resBody     = $this->request(self::API_PURCHASE, $param);//Submit to the gateway
        if($resBody != '' || $resBody !=' '){
		$this->res = json_decode($resBody, true);
	}
	return  $this->res;
    }

    public function withToken($cardid, $mchorderno, $amount,$notifyurl='https://localhost')//purchase with token
    {
        $arr = array(
            'mchId'      => self::MCH_ID,
            'mchOrderNo' => $mchorderno,
            'cardId'     => $cardid,
            'amount'     => intval($amount * 100),
            'currency'   => 'CAD',
            'loginName'  => self::LOGIN_NAME,
	    'notifyUrl'  => $notifyurl;
        );
        $sort_array  = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");
        $param       = json_encode($arr);
        $resBody     = $this->request(self::API_WITHTOKEN, $param);
	if($resBody != '' || $resBody !=' '){
		$this->res = json_decode($resBody, true);
	}
        return $this->res;
    }
	
    public function withWallet($cardid, $mchorderno, $amount,$walletdata,$wallettype)
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
        $resBody     = $this->request(self::API_WITHWALLET, $param);
	if($resBody != '' || $resBody !=' '){
		$this->res = json_decode($resBody, true);
	}
        return $this->res;
    }
	
    public function queryOrder($payorderid,$mchorderno)
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
        $resBody     = $this->request(self::API_QUERYORDER, $param);
	if($resBody != '' || $resBody !=' '){
		$this->res = json_decode($resBody, true);
	}
        return $this->res;
    }
	
    public function void($originalorderid,$mchrefundno,$notifyurl='https://localhost')
    {
        $arr = array(
            'mchId'       => self::MCH_ID,
            'mchRefundNo' => $mchrefundno,
            'loginName'   => self::LOGIN_NAME,
            'payOrderId'  => $originalorderid,
	    'notifyUrl'   => $notifyurl,
        );
        $sort_array = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");//Generate signature parameter sign
        $param = json_encode($arr);
        $resBody = $this->request(self::API_VOID, $param);
	if($resBody != '' || $resBody !=' '){
		$this->res = json_decode($resBody, true);
	}
        return $this->res;       
    }
	
    public function refund($originalorderid,$mchrefundno,$amount,$notifyurl='https://localhost')
    {
        $arr = array(
            'mchId'       => self::MCH_ID,
            'mchRefundNo' => $mchrefundno,
            'loginName'   => self::LOGIN_NAME,
            'payOrderId'  => $originalorderid,
            'refundAmount' => intval($amount * 100),
	    'notifyUrl'    => $notifyurl,
        );
        $sort_array = $this->arg_sort($arr);
        $arr['sign'] = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");//Generate signature parameter sign
        $param = json_encode($arr);
        $resBody = $this->request(self::API_REFUND, $param);
	if($resBody != '' || $resBody !=' '){
		$this->res = json_decode($resBody, true);
	}
        return $this->res;      
    }
	
    public function receiveNotification()
    {
	$content = file_get_contents("php://input");
	if($content != '' || $content !=' '){
		return $this->res;
	}
        $arr = json_decode(file_get_contents("php://input"),true);
        $backsign = $arr['sign'];
        unset($arr["sign"]);
        $sort_array = $this->arg_sort($arr);
        $mysign     = $this->build_mysign($sort_array, self::MCH_KEY, "MD5");
        if ($mysign == $backsign) {           
            echo "SUCCESS";//DO NOT DELETE
            $this->res['retCode'] = 'SUCCESS';
            $this->res['retData'] = $arr;
            return $this->res;;
        }else {
            echo "FAIL";//DO NOT DELETE
            $this->res['retCode'] = 'FAIL';
            $this->res['retData'] = $arr;
            return $this->res;;
        }
    }
}
	
