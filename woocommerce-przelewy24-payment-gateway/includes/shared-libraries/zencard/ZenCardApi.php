<?php

require_once dirname(__FILE__) . '/Util.php';
require_once dirname(__FILE__) . '/Transaction.php';

if (!class_exists('ZenCardApi', false)) {
    class ZenCardApi
    {
        const USER_AGENT = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';
        const METHOD_POST = 'POST';
        const METHOD_GET = 'GET';
        const API_BASE_URL = 'https://secure.przelewy24.pl/api/v1/zencard/';

        /**
         * @var int
         */
        private $merchantId;

        /**
         * @var string
         */
        private $url;

        /**
         * @var string
         */
        private $post;

        /**
         * @var string
         */
        private $method;

        /**
         * @var string
         */
        private $apiKey;

        /**
         * @var
         */
        private $response;

        /**
         * @var array
         */
        private $headers = array();

        /**
         * @var string
         */
        private $userAgent;

        /**
         * ZenCardApi constructor.
         * @param $merchantId
         * @param $apiKey
         */
        public function __construct($merchantId, $apiKey)
        {
            $this->merchantId = (int)$merchantId;
            $this->apiKey = (string)$apiKey;

            $this->method = self::METHOD_POST;
            $this->headers[] = $this->getBasicAuthHeader();
            $this->userAgent = Zencard_Util::getRemoteUserAgent();
        }

        /**
         * @return bool
         */
        public function isEnabled()
        {
            $this->url = self::API_BASE_URL . 'isenabled';
            $this->method = self::METHOD_GET;
            $this->call();
            $decodedResponse = json_decode($this->response);

            return (empty($decodedResponse->error) && $decodedResponse->code === 200);
        }

        /**
         * @return string
         */
        public function getScript()
        {
            $this->url = self::API_BASE_URL . 'getscriptname';
            $this->method = self::METHOD_GET;
            $this->call();
            $decodedResponse = json_decode($this->response);

            return html_entity_decode($decodedResponse->url);
        }

        /**
         * @param $email
         * @param $amount
         * @param $zenCardOrderId
         * @return Transaction
         */
        public function verify($email, $amount, $zenCardOrderId)
        {
            $data = $this->getTransactionData($email, $amount, $zenCardOrderId);
            $transaction = new Transaction($data);

            return $transaction;
        }

        /**
         * @param $zenCardOrderId
         * @param $amount
         * @return Transaction
         */
        public function confirm($zenCardOrderId, $amount)
        {
            $data = $this->sendConfirmation($zenCardOrderId, $amount);
            $transaction = new Transaction($data);

            return $transaction;
        }

        /**
         * @param $storeOrderId
         * @param $storeUrl
         * @return string
         */
        public function buildZenCardOrderId($storeOrderId, $storeUrl)
        {
            return md5($this->merchantId . '_' . $storeOrderId . '_' . $storeUrl);
        }

        /**
         * @return mixed
         */
        private function call()
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, $this->method === self::METHOD_POST);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->post);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_URL, $this->url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
            curl_setopt($curl, CURLOPT_USERAGENT, !is_null($this->userAgent) ? $this->userAgent : self::USER_AGENT);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $this->response = curl_exec($curl);
            curl_close($curl);

            return $this->response;
        }

        /**
         * @return string
         */
        private function getBasicAuthHeader()
        {
            return 'Authorization: Basic ' . base64_encode($this->merchantId . ':' . $this->apiKey);
        }

        /**
         * @param $email
         * @param $amount
         * @param $zenCardOrderId
         * @return stdClass
         */
        private function getTransactionData($email, $amount, $zenCardOrderId)
        {
            $this->url = self::API_BASE_URL . 'verify';
            $this->method = self::METHOD_POST;

            $cookies = array(
                'zcodeCookie' => Zencard_Util::getCookie(Zencard_Util::ZCODE_COOKIE, ''),
                'extraInfoCookie' => Zencard_Util::getCookie(Zencard_Util::EXTRA_INFO_COOKIE, ''),
                'discountCookie' => Zencard_Util::getCookie(Zencard_Util::DISCOUNT_COOKIE, '')
            );

            $data = array(
                'p24_email' => $email,
                'p24_amount' => $amount,
                'p24_cookie' => json_encode($cookies),
                'p24_ip' => Zencard_Util::getRemoteIp(),
                'p24_order_id' => $zenCardOrderId,
                'p24_amount_discount' => Zencard_Util::getAmountWithDiscount(number_format($amount, 0, "", ""))
            );

            $this->post = http_build_query($data);
            $this->call();
            $decodedResponse = json_decode($this->response);
            $transaction = json_decode($decodedResponse->transaction);
            $transaction = ($transaction instanceof stdClass) ? $transaction : new stdClass();

            return $transaction;
        }

        /**
         * @param $zenCardOrderId
         * @param $amount
         * @return stdClass
         */
        private function sendConfirmation($zenCardOrderId, $amount)
        {
            $this->url = self::API_BASE_URL . 'confirm';
            $this->method = self::METHOD_POST;

            $data = array(
                'p24_amount' => $amount,
                'p24_order_id' => $zenCardOrderId,
                'p24_ip' => Zencard_Util::getRemoteIp()
            );

            $this->post = http_build_query($data);
            $this->call();
            $decodedResponse = json_decode($this->response);
            $transaction = json_decode($decodedResponse->transaction);
            $transaction = ($transaction instanceof stdClass) ? $transaction : new stdClass();

            return $transaction;
        }
    }
}