# monetivo Magento 1.9 Plugin

## Wstęp

To repozytorium zawiera kod moduły płatności monetivo dla Magento w wersji 1.9. 
Aby zainstalować moduł skorzystaj z poniższej instrukcji.
Jeżeli jestes developerem i chciałbyś pomóc (super!) to serdecznie zapraszamy! 

## Wymagania i zależności

- Magento w wersji **1.9.x**
- konto Merchanta w monetivo ([załóż konto](https://merchant.monetivo.com/register))

Moduł korzysta z naszego [klienta PHP](https://github.com/monetivo/monetivo-php) zatem wymagania środowiska są tożsame, tj. PHP w wersji 5.5 lub wyższej.
Dodatkowo potrzebne są moduły PHP:
- [`curl`](https://secure.php.net/manual/en/book.curl.php),
- [`json`](https://secure.php.net/manual/en/book.json.php)

## Instalacja

1. [Pobierz archiwum ZIP](https://merchant.monetivo.com/download/monetivo-magento.zip) z wtyczką na dysk.
2. Zawartość paczki skopiuj do głownego katalogu Magento używając SFTP/FTP. 
3. Wyczyść cache **Flush Magento Cache** „System > Cache Management".
3. Nowo zainstalowany moduł pojawi się na liście „System > Configruation" w sekcji **Sales**.

## Konfiguracja
1.	Przejdźdo „System > Configruation". Z lewej strony znajdź sekcję **Sales** i wybierz **Payment Methods**.
2.	Otworzy się nowy widok z dostępnymi metodami płatności. Wybierz **monetivo**.
3.	Skonfiguruj bramkę podając dane uzyskane w Panelu Merchanta:
   - Wpisz dane: Login użytkownika, Hasło oraz Token aplikacji.
4. Zapisz zmiany.

## Changelog

1.0.0 2017-06-26

- Wersja stabilna

## Błędy

Jeżeli znajdziesz jakieś błędy zgłoś je proszę przez GitHub. Zachęcamy również do zgłaszania pomysłów dotyczących ulepszenia naszych rozwiązań.

## Wsparcie
W razie problemów z integracją prosimy o kontakt z naszym supportem. 