# Adım 3 — Kodun yeni env yapısına uyarlanması

## Amaç

Kodda kalan eski `ENVIROMENT` sabitini ve `"production"`/`"development"` karşılaştırmalarını kaldırmak ve
Firebase servis hesabı dosyasının yolunu env'den okumak.

## Değişen dosyalar

| Dosya | Önce | Sonra | Etkisi |
|---|---|---|---|
| `Kernel/CoreDB.php` | `ENVIROMENT != "production"` | `!Environment::isProd()` | `dev` ve `staging`'de mailler test adresine gider |
| `Kernel/Src/Form/Form.php` | `defined("ENVIROMENT") && ENVIROMENT != "development"` | `!Environment::isDev()` | Formun render cache'i `dev` dışındaki ortamlarda açık |
| `Kernel/Src/Theme/View.php` (2 yer) | aynı | `!Environment::isDev()` | View render cache'i `dev` dışındaki ortamlarda açık |
| `Kernel/Src/Theme/CoreRenderer.php` | `in_array($enviroment, ["production", "staging"])` / `== "development"` | `$isDev = Environment::isDev()` | `dev`'de Twig debug ve `DebugExtension`, diğerlerinde Twig cache |
| `Kernel/Src/Lib/PushNotification/PushNotificationService.php` | `__DIR__ . '/../../../../config/firebase-service-account.json'` | `FIREBASE_CREDENTIALS_PATH` | Dosya yolu env'den ayarlanabiliyor (Docker'da secret olarak mount etmek için) |

Davranış değişmedi:

| Eski değer | Yeni değer | Twig cache | Mail |
|---|---|---|---|
| `production` | `prod` | açık | gerçek alıcıya |
| `staging` | `staging` | açık | test adresine |
| `development` | `dev` | kapalı, debug açık | test adresine |

## Öğrendiklerimiz / dikkat edilecekler

1. **`defined()` kontrollerine artık gerek yok.** Eskiden `config.php` olmayabilirdi, bu yüzden her yerde
   `defined("ENVIROMENT")` kontrolü vardı. Artık `Environment::load()` her istekte çalışıyor ve geçersiz bir
   `APP_ENV` değerinde uygulama hiç açılmıyor. Bu yüzden `APP_ENV` her zaman tanımlı ve geçerli.
2. **Karşılaştırmalar tek yerde.** Kodda `"prod"` gibi string'ler dağınık durmuyor; `Environment::isDev()` ve
   `Environment::isProd()` kullanılıyor. İleride bir ortam adı değişirse sadece `Kernel/Environment.php` değişir.
3. **İsim çakışması.** `CoreRenderer.php` zaten `Twig\Environment` sınıfını `Environment` adıyla import ediyor.
   Bu yüzden orada tam sınıf adı kullanıldı: `\CoreDB\Kernel\Environment::isDev()`.
4. **CLI komutları bile DB'ye bağlanıyor.** `php bin/console.php list` komutu bile DB'ye bağlanıyor, çünkü komut açıklamaları
   `translations` tablosundan çevriliyor. 5. adımda container'ın entrypoint'ini yazarken boş bir DB'de
   `config:import`'un çalışıp çalışmadığını kontrol etmek gerekecek.

## Doğrulama

- `grep ENVIROMENT` → `Kernel`, `App`, `bin`, `public_html` ve `bootstrap.php` içinde hiç sonuç yok.
  Kalan tek yer `config/config_example.php`; o dosya 4. adımda silinecek.
- Değişen dosyalarda `php -l` temiz. `phpcs --standard=PSR12` sonuçları değişiklikten öncekiyle aynı (temiz).
  `Kernel/CoreDB.php` zaten `phpcs:ignoreFile` ile işaretli.
- `Environment::isDev()` → `true`, `isProd()` → `false`, `FIREBASE_CREDENTIALS_PATH` → proje klasörüne göre çözülmüş mutlak yol.
- `php bin/console.php list` → DB bilgilerini `.env`'den okuyup `mysql` sunucusuna bağlanmayı denedi.
  Docker dışında `mysql` adresi çözülemediği için bağlantı hatası verdi; bu beklenen bir sonuç.
  Gerçek uçtan uca test 6. adımda Docker ile yapılacak.
