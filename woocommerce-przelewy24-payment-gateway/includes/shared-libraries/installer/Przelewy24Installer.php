<?php

if (!class_exists('Przelewy24Installer', false)) {
    class Przelewy24Installer implements Przelewy24Interface
    {
        private $translations;
        private $sliderEnabled = true;
        private $pages = array();

        public function __construct($sliderEnabled = true, array $translations = array())
        {
            $this->sliderEnabled = $sliderEnabled;
            $this->setTranslations($translations);
        }

        public function setTranslations(array $translations = array())
        {
            $this->translations = $translations;

            // set default values
            if (empty($this->translations['php_version'])) {
                $this->translations['php_version'] = 'Wersja PHP min. 5.2';
            }
            if (empty($this->translations['curl_version'])) {
                $this->translations['curl_enabled'] = 'Włączone rozszerzenie PHP cURL (php_curl.dll)';
            }
            if (empty($this->translations['soap_enabled'])) {
                $this->translations['soap_enabled'] = 'Włączone rozszerzenie PHP SOAP (php_soap.dll)';
            }

            if (empty($this->translations['merchant_id'])) {
                $this->translations['merchant_id'] = 'ID sprzedawcy';
            }
            if (empty($this->translations['shop_id'])) {
                $this->translations['shop_id'] = 'ID sklepu';
            }
            if (empty($this->translations['crc_key'])) {
                $this->translations['crc_key'] = 'Klucz CRC';
            }
            if (empty($this->translations['api_key'])) {
                $this->translations['api_key'] = 'Klucz API';
            }
        }

        public function addPages(array $pages = array())
        {
            $this->pages = array_values($pages);
        }

        public function renderInstallerSteps()
        {
            if (!$this->sliderEnabled || empty($this->pages) || !is_array($this->pages)) {
                return '';
            }

            $requirements = $this->checkRequirements();
            $params = array(
                'requirements' => $requirements,
                'translations' => $this->translations
            );
            $maxSteps = 0;
            $data = array(
                'steps' => array()
            );
            foreach ($this->pages as $page) {
                $page = (int)$page;
                if ($page > 0) {
                    $step = $this->loadStep($page, $params);
                    $data['steps'][$page] = $step;
                    $maxSteps++;
                }
            }

            if ($maxSteps === 0) {
                return '';
            }
            $data['maxSteps'] = $maxSteps;

            return $this->loadTemplate('installer', $data);
        }

        private function loadStep($number, $params = null)
        {
            $step = $this->loadTemplate('step' . $number, $params);
            $step = $this->removeNewLines($step);
            return $step;
        }

        private function removeNewLines($string)
        {
            return trim(str_replace(PHP_EOL, ' ', $string));
        }

        private function loadTemplate($view, $data = null)
        {
            extract(array("content" => $data));
            ob_start();
            $viewFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . "$view.tpl.php";

            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                throw new Exception('View not exist in ' . get_class($this));
            }
            $content = ob_get_clean();
            return $content;
        }

        private function checkRequirements()
        {
            $data = array(
                'php' => array(
                    'test' => (version_compare(PHP_VERSION, '5.2.0') > 0),
                    'label' => $this->translations['php_version']
                ),
                'curl' => array(
                    'test' => function_exists('curl_version'),
                    'label' => $this->translations['curl_enabled']
                ),
                'soap' => array(
                    'test' => class_exists('SoapClient'),
                    'label' => $this->translations['soap_enabled']
                )
            );
            return $data;
        }
    }
}