<?php
/**
 * Przelewy24 comunication class
 * 
 * @author DialCom24 Sp. z o.o.
 * @copyright DialCom24 Sp. z o.o.
 * @version 1.1
 * @since 2014-04-29
 */

/**
 * 
 * Communication protol version
 * @var double
 */
define('P24_VERSION', '3.2');
if (class_exists('Przelewy24Class', false)!=true) {
class Przelewy24Class {
    /**
     * Live system URL address
     * @var string
     */
    private static $hostLive    = 'https://secure.przelewy24.pl/';
    /**
     * Sandbox system URL address
     * @var string
     */
    private static $hostSandbox = 'https://sandbox.przelewy24.pl/';
    /**
     * Use Live (false) or Sandbox (true) enviroment
     * @var bool
     */    
    private $testMode           = false;
    /**
     * Merchant Id
     * @var int
     */    
    private $merchantId         = 0;
    /**
     * Merchant posId
     * @var int
     */    
    private $posId              = 0;
    /**
     * Salt to create a control sum (from P24 panel)
     * @var string
     */    
    private $salt               = '';
    /**
     * API Key (from P24 panel)
     * @var string
     */    
    private $api                = '';
    /**
     * Array of POST data
     * @var array
     */    
    private $postData           = array();

    /**
     * Przelewy24Class constructor.
     * @param $merchantId
     * @param $posId
     * @param $salt
     * @param bool $testMode
     * @param string $apiKey
     */
    public function __construct($merchantId, $posId, $salt, $testMode = false, $apiKey = '') {
        $this->posId         = (int)trim($posId);
        $this->merchantId    = (int)trim($merchantId);
        if ($this->merchantId==0)
            $this->merchantId= trim($posId);
        $this->salt          = trim($salt);
        $this->testMode      = $testMode;

        $this->addValue('p24_merchant_id', $this->merchantId);
        $this->addValue('p24_pos_id', $this->posId);
        $this->addValue('p24_api_version', P24_VERSION);

		$this->api = $apiKey;
        
        return true;        
    }
    public function setApiKey($apiKey) {
		$this->api = $apiKey;
	}
    /**
     * Returns host URL
     */
    public function getHost() {
        if ($this->testMode) return self::$hostSandbox;
        return self::$hostLive;
    }
    /**
     * Returns URL for direct request (trnDirect)
     */
    public function trnDirectUrl() {
        return $this->getHost().'trnDirect';
    }
    /**
     * 
     * Add value do post request
     * @param string $name Argument name
     * @param mixed $value Argument value
     */
    public function addValue($name, $value) {
        if ($this->validateField($name, $value))
            $this->postData[$name] = $value;
    }
    
    /**
     * 
     * Function is testing a connection with P24 server
     * @return array Array(INT Error, Array Data), where data 
     */
    public function testConnection() {
        $crc = md5($this->posId."|".$this->salt);
        $ARG["p24_pos_id"] = $this->posId;
        $ARG["p24_sign"] = $crc;
        $RES = $this->callUrl("testConnection",$ARG);
        return $RES;
    }
    
    /**
     * 
     * Prepare a transaction request
     * @param bool $redirect Set true to redirect to Przelewy24 after transaction registration
     * @return array array(INT Error code, STRING Token)
     */
    public function trnRegister($redirect = false) {
        $this->addValue("p24_sign", $this->trnDirectSign($this->postData));
        $RES = $this->callUrl("trnRegister",$this->postData);
        if($RES["error"] == "0") {
            $token = $RES["token"];
        } else {
            return $RES;
        }
        if($redirect) {
            $this->trnRequest($token);
        }
        return array("error"=>0, "token"=>$token);
    }
    
    /**
     * Redirects or returns URL to a P24 payment screen
     * @param string $token Token
     * @param bool $redirect If set to true redirects to P24 payment screen. If set to false function returns URL to redirect to P24 payment screen
     * @return string URL to P24 payment screen
     */
    public function trnRequest($token, $redirect = true) {
        $url=$this->getHost().'trnRequest/'.$token;
        if($redirect) {
            header('Location: '.$url);
            return '';
        }
        return $url; 
    }
    
    /**
     * 
     * Function verify received from P24 system transaction's result.
     * @return array
     */
    public function trnVerify() {
        $crc = md5($this->postData["p24_session_id"]."|".$this->postData["p24_order_id"]."|".$this->postData["p24_amount"]."|".$this->postData["p24_currency"]."|".$this->salt);
        $this->addValue('p24_sign', $crc);
        $RES = $this->callUrl('trnVerify',$this->postData);
        return $RES;
    }

    /**
     * 
     * Function contect to P24 system
     * @param string $function Method name
     * @param array $ARG POST parameters
     * @return array array(INT Error code, ARRAY Result)
     */
    private function callUrl($function, $ARG) {
        
        if(!in_array($function, array('trnRegister','trnRequest','trnVerify','testConnection'))) {
            
            return array('error'=>201,'errorMessage'=>'class:Method not exists');
            
        }
        if ($function!='testConnection') $this->checkMandatoryFieldsForAction($ARG, $function);
        
        $REQ = array();
        
        foreach($ARG as $k=>$v) $REQ[] = $k."=".urlencode($v);
        $url = $this->getHost().$function;
        $user_agent = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';
        if(($ch = curl_init())) {
        
            if(count($REQ)) {
                curl_setopt($ch, CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,join("&",$REQ));
            }
            
            curl_setopt($ch, CURLOPT_URL,$url);
            if ($this->testMode) curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            else curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            if(($result = curl_exec ($ch))) {
                $INFO = curl_getinfo($ch);
                curl_close ($ch);
                
                if($INFO['http_code']!=200) {
                    
                    return array('error'=>200,'errorMessage'=>'call:Page load error ('.$INFO['http_code'].')');
                    
                } else {
                
                    $RES     = array();
     	            $X       = explode('&', $result);
              	
     	            foreach($X as $val) {
                	    
                 		$Y           = explode('=', $val);
                 		$RES[trim($Y[0])] = urldecode(trim($Y[1]));
                 	}
             	    return $RES;

                }
                
            	
            } else {
                curl_close ($ch);
                return array('error'=>203,'errorMessage'=>'call:Curl exec error');
                
            }
            
        } else {
            
            return array('error'=>202,'errorMessage'=>'call:Curl init error');
        
        }
    }
    
    private function validateVersion(&$version) {
        if (preg_match('/^[0-9]+(?:\.[0-9]+)*(?:[\.\-][0-9a-z]+)?$/',$version))
            return true;
        $version='';
        return false;
    }
    private function validateEmail(&$email) {
        if (($email=filter_var($email, FILTER_VALIDATE_EMAIL)))
            return true;
        $email='';
        return false;
    }
    private function validateNumber(&$value, $min = false, $max = false) {
        if (is_numeric($value)) {
            $value=(int)intval($value);
            if (($min!==false && $value < $min) || ($max!==false && $value > $max)) return false;
            return true;
        }
        $value=($min!==false?$min:0);
        return false;
    }
    private function validateString(&$value, $len=0) {
        if (preg_match("/<[^<]+>/", $value, $m) != 0) {
            return false;
        }

        if ($len==0 ^ strlen($value)<=$len)
            return true;
        $value='';
        return false;
    }
    private function validateUrl(&$url, $len=0) {
        if ($len==0 ^ strlen($url)<=$len)
            if(preg_match('@^https?://[^\s/$.?#].[^\s]*$@iS', $url))
                return true;
        $url='';
        return false;
    }
    private function validateEnum(&$value, $haystack) {
        if (in_array(strtolower($value), $haystack)) return true;
        $value=$haystack[0];
        return false;
    }
    /**
     * 
     * @param string $field
     * @param mixed &$value
     * @return boolean
     */
    public function validateField($field, &$value) {        
        $ret=false;
        switch ($field) {
            case 'p24_session_id':
                $ret=$this->validateString($value, 100);
                break;
            case 'p24_description':
                $ret=$this->validateString($value, 1024);
                break;
            case 'p24_address':
                $ret=$this->validateString($value, 80);
                break;
            case 'p24_country':
            case 'p24_language':
                $ret=$this->validateString($value, 2);
                break;
            case 'p24_client':
            case 'p24_city':
                $ret=$this->validateString($value, 50);
                break;
            case 'p24_merchant_id':
            case 'p24_pos_id':
            case 'p24_order_id':
            case 'p24_amount':
            case 'p24_method':
            case 'p24_time_limit':
            case 'p24_channel':
            case 'p24_shipping':
                $ret=$this->validateNumber($value);
                break;
            case 'p24_wait_for_result':
                $ret=$this->validateNumber($value, 0, 1);
                break;
            case 'p24_api_version':
                $ret=$this->validateVersion($value);
                break;
            case 'p24_sign':
                if (strlen($value)==32 && ctype_xdigit($value))
                    $ret=true;
                else 
                    $value='';
                break;
            case 'p24_url_return':
            case 'p24_url_cancel':
            case 'p24_url_status':
                $ret=$this->validateUrl($value, 250);
                break;
            case 'p24_currency':
                $ret=preg_match('/^[A-Z]{3}$/', $value);
                if (!$ret) $value='';
                break;
            case 'p24_email':
                $ret=$this->validateEmail($value);
                break;
            case 'p24_encoding':
                $ret=$this->validateEnum($value, array('iso-8859-2', 'windows-1250', 'urf-8', 'utf8'));
                break;
            case 'p24_transfer_label':
                $ret=$this->validateString($value, 20);
                break;
            case 'p24_phone':
                $ret=$this->validateString($value, 12);
                break;
            case 'p24_zip':
                $ret=$this->validateString($value, 10);
                break;
            default:
                if (strpos($field, 'p24_quantity_')===0 || strpos($field, 'p24_price_')===0 || strpos($field, 'p24_number_')===0) {
                    $ret=$this->validateNumber($value);
                } elseif (strpos($field, 'p24_name_')===0 || strpos($field, 'p24_description_')===0) {
                    $ret=$this->validateString($value, 127);
                } else $value='';
                break;
        }
        return $ret;
    }
    private function filterValue($field, $value) {
        return ($this->validateField($field, $value))? addslashes($value) : false;
    }    

    public function checkMandatoryFieldsForAction($fieldsArray, $action) {
        $keys=array_keys($fieldsArray);
        $verification=($action=='trnVerify');
        static $mandatory=array('p24_order_id',//verify
            'p24_sign','p24_merchant_id','p24_pos_id','p24_api_version','p24_session_id','p24_amount',//all
            'p24_currency','p24_description','p24_country','p24_url_return','p24_url_cancel','p24_currency','p24_email');//register/direct
      
        for ($i=($verification?0:1); $i<($verification?4:count($mandatory)); $i++) {
            if(!in_array($mandatory[$i], $keys)) {
                throw new Exception('Field '.$mandatory[$i].' is required for '.$action.' request!');
            }
        }
        return true;
    }
    /**
     * Parse and validate POST response data from Przelewy24
     * @return array - valid response | false - invalid crc | null - not a Przelewy24 response
     */
    public function parseStatusResponse() {
        if (isset($_POST['p24_session_id'], $_POST['p24_order_id'], $_POST['p24_merchant_id'], $_POST['p24_pos_id'], $_POST['p24_amount'], $_POST['p24_currency'], $_POST['p24_method']/*, $_POST['p24_statement']*/, $_POST['p24_sign'])) {
            $session_id  = $this->filterValue('p24_session_id', $_POST['p24_session_id']);
            $merchant_id = $this->filterValue('p24_merchant_id', $_POST['p24_merchant_id']);
            $pos_id      = $this->filterValue('p24_pos_id', $_POST['p24_pos_id']);
            $order_id    = $this->filterValue('p24_order_id', $_POST['p24_order_id']);
            $amount      = $this->filterValue('p24_amount', $_POST['p24_amount']);
            $currency    = $this->filterValue('p24_currency', $_POST['p24_currency']);
            $method      = $this->filterValue('p24_method', $_POST['p24_method']);
            $sign        = $this->filterValue('p24_sign', $_POST['p24_sign']);

            if ($merchant_id!=$this->merchantId || $pos_id!=$this->posId || md5($session_id.'|'.$order_id.'|'.$amount.'|'.$currency.'|'.$this->salt)!=$sign) return false;
            
            return array(
                'p24_session_id'  => $session_id,
                'p24_order_id'    => $order_id,
                'p24_amount'      => $amount,
                'p24_currency'    => $currency,
                'p24_method'      => $method,
            );
        }
        return null;
    }
    public function trnVerifyEx($data=null) {
        $a=$this->parseStatusResponse();
        if ($a===null) return null;
        elseif ($a) {
            if ($data!=null) {
                foreach ($data as $field => $value) {
                    if ($a[$field]!=$value) return false;
                }
            }
            $this->postData=array_merge($this->postData,$a);
            $result=$this->trnVerify();
            return ($result['error']==0);
        }
        return false;
    }
    /*
     * Zwraca p24_sign dla trnDirect
     * */
    public function trnDirectSign($data) {
        return md5($data['p24_session_id'].'|'.$this->posId.'|'.$data['p24_amount'].'|'.$data['p24_currency'].'|'.$this->salt);
    }

	/*
	 * Zwraca listę kanałów płatności, którymi można płacić inną walutą niż PLN
	 * */
	public static function getChannelsNonPln() {
		return array(124,140,145,152,66,92);
	}
	/*
	 * Zwraca listę kanałów płatności ratalnej
	 * */
	public static function getChannelsRaty() {
		return array(72,129,136);
	}

	/*
	 * Zwraca minimalną kwotę dla płatności ratalnych
	 * */
	public static function getMinRatyAmount() {
		return 300;
	}		

	/*
	 * Zwraca listę kanałów płatności kartą
	 * */
	public static function getChannelsCard() {
		return array(140,142,145);
	}

	/*
	 * końcówka adresu WSDL
	 * */
    private function getWsdlService() {
		return 'external/'.(int)$this->merchantId.'.wsdl';
	}

	/*
	 * końcówka adresu WSDL dla kart
	 * */
    static function getWsdlCCService() {
		return 'external/wsdl/charge_card_service.php?wsdl';
	}

	/*
	 * czy Merchant ma dostęp do rekurencji na kartach
	 * */
	public function ccCheckRecurrency() {
		try {
			$s=new SoapClient($this->getHost().$this->getWsdlCCService(), array('trace'=>true, 'exceptions'=>true, 'connection_timeout' => 2));
			$res=$s->checkRecurrency($this->merchantId, $this->salt);
		} catch (Exception $e) {
			error_log(__METHOD__.' '.$e->getMessage());
		}
		return !!$res;
	}
	/*
	 * czy Merchant wpisał poprawny klucz api
	 * */
	public function apiTestAccess() {
		if (empty($this->api)) return false;
		try {
			$s=new SoapClient($this->getHost().$this->getWsdlService(), array('trace'=>true, 'exceptions'=>true, 'connection_timeout' => 2));
			$res=$s->TestAccess($this->merchantId, $this->api);
		} catch (Exception $e) {
			error_log(__METHOD__.' '.$e->getMessage());
		}
		return !!$res;
	}

	/*
	 * Zwraca listę kanałów płatności [id => etykieta,]
	 *
	 * $only24at7 - płatności, które są w tej chwili aktywne - usuwa z wyników te nienatychmiastowe
	 * $currency - ogranicza listę metod płatności do dostępnych dla wskazanej waluty
	 * $lang - Etykiety kanałów płatności w wybranym języku
	 * 
	 * */
	public function availablePaymentMethods($only24at7 = true, $currency = 'PLN', $lang = 'pl') {
		if (empty($this->api)) return false;
		try {
            $s = new SoapClient($this->getHost() . $this->getWsdlService(), array('trace' => true, 'exceptions' => true, 'connection_timeout' => 2));
            $res = $s->PaymentMethods($this->merchantId, $this->api, $lang);
		} catch (Exception $e) {
		    error_log(__METHOD__.' '.$e->getMessage());
		}
        if (!empty($res) && isset($res->error) && $res->error->errorCode === 0) {

			if ($only24at7) {
				foreach ($res->result as $key => $item) {
					if (!$item->status || in_array($item->id, array(1000))) {
						unset($res->result[$key]);
					}
				}
			}
			if ($currency != 'PLN') {
				foreach ($res->result as $key => $item) {
					if (!in_array($item->id, $this->getChannelsNonPln())) {
						unset($res->result[$key]);
					}
				}
			}
			return $res->result;
		}
		return false;
	}
	public function availablePaymentMethodsSimple($only24at7 = true, $currency = 'PLN', $lang = 'pl') {
		$all = $this->availablePaymentMethods($only24at7, $currency, $lang);
		$result = array();
		if (is_array($all) && sizeof($all) > 0) {
			foreach ($all as $item) {
				$result[$item->id] = $item->name;
			}
		} else {
			$result = $all;
		}
		return $result;
	}
	public static function requestGet($url) {
		$isCurl = function_exists('curl_init')&&function_exists('curl_setopt')&&function_exists('curl_exec')&&function_exists('curl_close');

		if ($isCurl) {
			$userAgent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
			$curlConnection = curl_init();
			curl_setopt($curlConnection, CURLOPT_URL, $url);
			curl_setopt($curlConnection, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($curlConnection, CURLOPT_USERAGENT, $userAgent);
			curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curlConnection, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($curlConnection);
			curl_close($curlConnection);
			return $result;
		}
		return "";
	}
}
}
