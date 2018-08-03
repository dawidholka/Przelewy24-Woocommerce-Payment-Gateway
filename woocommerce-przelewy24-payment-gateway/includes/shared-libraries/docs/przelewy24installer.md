# klasa `Przelewy24Installer` #

## funkcja: `renderInstallerSteps()` ##

Generuje gotowy kod HTML z instalatorem, który wystarczy wyświetlić w odpowiednim miejscu templatki sklepu.

## funkcja: `addPages(array)` ##

Funkcja na wejście przyjmuje tablicę z numerami stron instalatora, które mają być wyświetlone.
Funkcja nic nie zwraca, ustawia tylko strony jakie ma instalator przetwarzać w funkcji `renderInstallerSteps()`.

## Jak używać? ##

Przykład wygenerowania instalatora, który zawiera strony: 1, 2, 3:

```php
$installer = new Przelewy24Installer();
$installer->addPages(array(1, 2, 3));
$installer->setTranslations($translations);
$installerHtml = $installer->renderInstallerSteps();
```
