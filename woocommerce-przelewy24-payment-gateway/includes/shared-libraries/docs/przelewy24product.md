# class `Przelewy24Product` #

## function: `__construct()` ##

```php
- Constructor parameters:
@param $translations - texts for products - example input:
array(
    'virtual_product_name' => 'Virtual Product',
    'cart_as_product' => 'Order',
)
```

## function: `prepareCartItems()` ##

### Prepare cart items for form trnRegister (Przelewy24). ###
Documentation Przelewy24: 

https://przelewy24.pl/storage/app/media/pobierz/Instalacja/przelewy24_dokumentacja_3.2.pdf

5.1 (in documentation) - "Rejestracja transakcji".

Fields (from Przelewy24 documentation):
```php
p24_name_X - Product name
p24_description_X - Product description
p24_quantity_X - Product quantity
p24_price_X - Product price (X gr) - netto
p24_number_X - Product ID in shop

- Function prepareCartItems() parameters:
@param $amount - cart amount, amount to pay // x zł -> 100 gr

@param $items - example input:
array(
    array(
        'name' => 'Product X',
        'description' => 'Product details',
        'quantity' => '5',
        'price' => '500', // 5 zł -> 100 gr
        'number' => '12345',
    ),
    array(
        ...
    ),
)
@param $shipping - shipping coast // x zł -> 100 gr

@return array - cart items for p24_form. F.e:
    array(
        'p24_name_1' => 'Product X',
        'p24_description_1' => 'Product details',
        'p24_quantity_1' => '5',
        'p24_price_1' => '500',
        'p24_number_1' => '12345',
    )
```

## Jak używać? ##

```php
$p24Product = new Przelewy24Product($translations);
$p24ProductItems = $p24Product->prepareCartItems($amount, $productsInfo, $shipping);
```