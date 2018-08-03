<?php
if (!class_exists('Transaction', false)) {
    class Transaction
    {
        /**
         * @var string
         */
        private $userEmail;
        /**
         * @var bool
         */
        private $zencard;
        /**
         * @var bool
         */
        private $verified;
        /**
         * @var bool
         */
        private $confirmed;
        /**
         * @var bool
         */
        private $discount;
        /**
         * @var string
         */
        private $info;
        /**
         * @var int
         */
        private $amount;
        /**
         * @var int
         */
        private $amountWithDiscount;
        /**
         * @var bool
         */
        private $skipCoupon;

        /**
         * @var bool
         */
        private $hasCoupon;

        /**
         * Transaction constructor.
         *
         * @param mixed $data
         */
        public function __construct($data)
        {
            $this->setData($data);
        }

        /**
         * @return string
         */
        public function getUserEmail()
        {
            return $this->userEmail;
        }

        /**
         * @return boolean
         */
        public function withZencard()
        {
            return $this->hasCoupon;
        }

        /**
         * @return boolean
         */
        public function isVerified()
        {
            return $this->verified;
        }

        /**
         * @return boolean
         */
        public function isConfirmed()
        {
            return $this->confirmed;
        }

        /**
         * @return boolean
         */
        public function hasDiscount()
        {
            return $this->discount;
        }

        /**
         * @return string
         */
        public function getInfo()
        {
            return $this->info;
        }

        /**
         * @return int
         */
        public function getAmount()
        {
            return $this->amount;
        }

        /**
         * @return int
         */
        public function getAmountWithDiscount()
        {
            return $this->amountWithDiscount;
        }

        /**
         * @return float
         */
        public function getDiscountAmountFloat()
        {
            return ($this->getAmount() - $this->getAmountWithDiscount()) / 100;
        }

        /**
         * @return boolean
         */
        public function isSkipCoupon()
        {
            return $this->skipCoupon;
        }

        /**
         * @param mixed $data
         * @return boolean
         */
        private function setData($data)
        {
            if (empty($data) || !is_object($data)) {
                return false;
            }
            $this->userEmail = (isset($data->_identity) && isset($data->_identity->_user)) ? (string)$data->_identity->_user : '';
            $this->zencard = isset($data->_zencard) ? (bool)$data->_zencard : false;
            $this->verified = isset($data->_verified) ? (bool)$data->_verified : false;
            $this->confirmed = isset($data->_confirmed) ? (bool)$data->_confirmed : false;
            $this->discount = (isset($data->_discount) && $data->_discount instanceof \stdClass) ? (bool)$data->_discount : false;
            $this->amount = isset($data->_amount) ? (int)$data->_amount : 0;
            $this->amountWithDiscount = isset($data->_amountWithDiscount) ? (int)$data->_amountWithDiscount : 0;
            $this->info = isset($data->_info) && isset($data->_info->data) && isset($data->_info->data->info) ? (string)$data->_info->data->info : '';
            $this->skipCoupon = isset($data->_skipCoupon) ? (bool)$data->_skipCoupon : false;
            $this->hasCoupon = isset($data->_info) && isset($data->_info->data) && isset($data->_info->data->hasCoupon) ? (bool)$data->_info->data->hasCoupon : false;
            return true;
        }
    }
}