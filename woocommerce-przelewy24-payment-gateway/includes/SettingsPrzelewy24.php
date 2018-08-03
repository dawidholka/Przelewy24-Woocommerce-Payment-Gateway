<?php

class SettingsPrzelewy24
{
	
    const VISIBILITY_VISIBLE = 'visibility: visible';

    const VISIBILITY_HIDDEN = 'visibility: hidden';

    const TITLE = 'title';

    const TYPE = 'type';

    const DEFAULT_SETTING = 'default';

    const DESCRIPTION = 'description';

    const WOOCOMMERCE = 'woocommerce';

    const SELECT = 'select';

    const OPTIONS = 'options';

    const DESC_TIP = 'desc_tip';

	function get_options()
    {
        $option_list = array();
        $option_list['secure'] = __('Normalny', static::WOOCOMMERCE);
        $option_list['sandbox'] = __('Sandbox', static::WOOCOMMERCE);

        return $option_list;
    }
	
    public function getSettings()
    {
        $ukryjD = static::VISIBILITY_VISIBLE;
        $ukryjK = static::VISIBILITY_VISIBLE;
        $ukryjS = static::VISIBILITY_VISIBLE;

        if ($charge == '0') {
            $ukryjD = static::VISIBILITY_HIDDEN;
        }
        if ($list == '1') {
            $ukryjK = static::VISIBILITY_HIDDEN;
        }
        if ($tiles == '1') {
            $ukryjS = static::VISIBILITY_HIDDEN;
        }

        return array(
            'enabled' => array(
                static::TITLE           => __('Włącz/Wyłącz', static::WOOCOMMERCE),
                static::TYPE            => 'checkbox',
                'label'                 => __('Włącz metodę płatności przez przelewy24.', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => 'yes',
                static::DESCRIPTION     => sprintf(__(' <a href="%s" TARGET="_blank">Przelewy24</a>.', static::WOOCOMMERCE), 'https://przelewy24.pl/'),
            ),

            static::TITLE       => array(
                static::TITLE           => __('Nazwa', static::WOOCOMMERCE),
                static::TYPE            => 'text',
                static::DEFAULT_SETTING => __('Przelewy24', static::WOOCOMMERCE),
                static::DESC_TIP        => true,
            ),
            static::DESCRIPTION => array(
                static::TITLE           => __('Opis', static::WOOCOMMERCE),
                static::TYPE            => 'textarea',
                static::DESCRIPTION     => __('Ustawia opis bramki, który widzi użytkownik przy tworzeniu zamówienia.'
                    , static::WOOCOMMERCE),
                static::DEFAULT_SETTING => __('System płatności Przelewy24 to bezpieczny i szybki sposób płatności,
                 który został wybrany przez Odbiorcę płatności w celu przyjęcia od Ciebie zapłaty.'
                    , static::WOOCOMMERCE)
            ),
            'description'              => array(
                static::TITLE           => __('Tytuł transakcji', static::WOOCOMMERCE),
                static::TYPE            => 'text',
                static::DESCRIPTION     => __('Tekst który zobaczą klienci podczas dokonywania zakupu.', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => __(''
                    , static::WOOCOMMERCE)
            ),
            'merchant_id'         => array(
                static::TITLE           => __('ID sprzedawcy', static::WOOCOMMERCE),
                static::TYPE            => 'text',
                static::DESCRIPTION     => __('Identyfikator sprzedawcy nadany w systemie Przelewy24.', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                static::DESC_TIP        => true,
            ),
			'shop_id'         => array(
                static::TITLE           => __('ID sklepu', static::WOOCOMMERCE),
                static::TYPE            => 'text',
                static::DESCRIPTION     => __('Identyfikator sklepu nadany w systemie Przelewy24.', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                static::DESC_TIP        => true,
            ),
			'CRC_key'         => array(
                static::TITLE           => __('Klucz CRC', static::WOOCOMMERCE),
                static::TYPE            => 'text',
                static::DESCRIPTION     => __('Klucz do CRC nadany w systemie Przelewy24.', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                static::DESC_TIP        => true,
            ),
			'p24_testmod'         => array(
                static::TITLE           => __('Tryb pracy modułu', static::WOOCOMMERCE),
                'type' => 'select',
                static::DESCRIPTION     => __('Tryb sandbox służy do testowania działania płatności przez bramkę.', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => 1,
                static::OPTIONS => $this->get_options(),
                static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                static::DESC_TIP        => true,
            ),
        );
    }
}
