# Adım 2 — Env loader: `config.php` yerine `.env` dosyaları

## Amaç

Ayarlar `config/config.php` dosyasındaki `define()` satırlarından değil, ortam değişkenlerinden ve `.env` dosyalarından gelsin.
Framework'ün geri kalanı aynı global sabitleri kullanmaya devam ettiği için kodun ~25 farklı yerine dokunmak gerekmedi.

## Değişen dosyalar

| Dosya                             | Değişiklik                                                                    |
| --------------------------------- | ----------------------------------------------------------------------------- |
| `composer.json` / `composer.lock` | `symfony/dotenv ^6.4` eklendi. `config.platform.php = 8.4` ayarlandı.         |
| `Kernel/Environment.php` (yeni)   | Env değerlerini okuyup sabitleri `define()` eden sınıf.                       |
| `.env` (yeni, git'te)             | Docker ile hiçbir ayar yapmadan çalışacak blueprint değerleri.                |
| `bootstrap.php`                   | `config.php` include'u kaldırıldı, yerine `Environment::load(__DIR__)` geldi. |
| `.gitignore`                      | `.env.local`, `.env.*.local`, `compose.override.yml` eklendi.                 |

## Nasıl çalışıyor?

Yükleme sırası aşağıdaki gibi; her biri öncekinin üzerine yazar:

```
.env  →  .env.local  →  .env.{APP_ENV}  →  .env.{APP_ENV}.local  →  gerçek ortam değişkenleri
```

- `.env` git'te durur. İçinde gizli bilgi olmamalı.
- `*.local` dosyaları gitignore'da. Her makinenin ya da ortamın kendi değerleri bunlara yazılır.
- Docker compose'un ya da CI'ın verdiği ortam değişkenleri her dosyanın üzerine yazar.

Sabitlerin türleri:

| Tür                | Örnek                                          | Nasıl okunuyor                                                            |
| ------------------ | ---------------------------------------------- | ------------------------------------------------------------------------- |
| string             | `DB_SERVER`, `LANGUAGE`                        | Olduğu gibi                                                               |
| boolean            | `PWA_ENABLED`, `HTTP_AUTH_ENABLED`             | `true/false/1/0/yes/no` → `filter_var(FILTER_VALIDATE_BOOLEAN)`           |
| liste              | `LOGIN_POLICY_ROLES=Admin,User`                | Virgülle bölünür ve kırpılır                                              |
| yol                | `FIREBASE_CREDENTIALS_PATH`                    | Göreli yol verilirse proje klasörüne göre çözülür                         |
| zorunlu            | `DB_SERVER`, `DB_NAME`, `DB_USER`, `HASH_SALT` | Boşsa açıklayıcı bir hata fırlatılır                                      |
| tanımsız kalabilen | `TIMEZONE`, `TRUSTED_HOSTS`                    | Boşsa `define` edilmez, çünkü kullanan kod `defined()` ile kontrol ediyor |

`PWA_MANIFEST` dizisi artık tek bir değer değil. `PWA_NAME`, `PWA_SHORT_NAME`, `PWA_DESCRIPTION`, `PWA_THEME_COLOR`, `PWA_BACKGROUND_COLOR` değişkenlerinden oluşturuluyor. `SITE_ROOT` sadece web isteklerinde bilindiği için manifest de sadece web isteklerinde tanımlanıyor.

`APP_ENV` sadece `dev`, `staging` ya da `prod` olabilir. Eski `ENVIROMENT` sabitinin yerini alıyor; kodun buna uyarlanması 3. adımda yapılacak.

## Öğrendiklerimiz / dikkat edilecekler

1. **PHP sürümü 8.4'e kilitli.** `Kernel/Database/MySQL/MySQLDriver.php` dosyasındaki `Pdo\Mysql::ATTR_USE_BUFFERED_QUERY`
   PHP 8.4 ile geldi. `kreait/firebase-php` ise en fazla 8.4'e izin veriyor. `config.platform.php` ayarı sayesinde
   makinede hangi PHP olursa olsun composer sürümleri 8.4'e göre seçiyor.
2. **Apache ortam değişkenlerini `$_ENV`'ye koymuyor.** Dotenv, `$_ENV`'de gördüğü değişkenlere dokunmaz.
   Önlem alınmasaydı Docker'ın verdiği değişkenleri `.env` dosyası ezerdi. Loader'daki `$_ENV += getenv();` satırı bunu çözüyor.
3. **`.env` sözdizimi.** `#` yorum başlatır (`"#fff"` tırnaklı yazılmalı), `$` değişken açar, boşluk içeren değerler tırnak ister.
   Salt bu yüzden hex üretildi.
4. **`HASH_SALT` koruması.** `.env` herkese açık olduğu için içindeki salt sadece development içindir.
   `APP_ENV=prod` iken salt hâlâ `.env`'deki değerse uygulama başlamaz.
   Salt değişirse şifrelenmiş veriler (`EncryptedModel`, JWT) çözülemez.

## Doğrulama

Loader, `.env`'nin geçici bir kopyasıyla şu senaryolarda test edildi; hepsi beklendiği gibi çalıştı:

- Sadece `.env` → blueprint değerleri
- `.env.local` → `.env`'nin üzerine yazdı
- Gerçek env var → `.env.local`'in de üzerine yazdı
- `APP_ENV=prod` ve blueprint salt → hata verdi
- `APP_ENV=prod` ve `.env.prod.local` → dosya yüklendi; `staging`'de yüklenmedi
- Geçersiz `APP_ENV` ve boş `DB_SERVER` → açıklayıcı hata verdi

`php -l` ve `phpcs --standard=PSR12` temiz geçti. `bootstrap.php`'deki tek phpcs uyarısı değişiklikten önce de vardı.

## Geçici durum

- `bootstrap.php` içindeki `define("CONFIGURATON_LOADED", true);` satırı 4. adımda (kurulum ekranının kaldırılması) silinecek.
- Kodun bazı yerleri hâlâ eski `ENVIROMENT` sabitini kullanıyor; 3. adımda düzeltilecek.
  Bu commit'ten sonra ve 3. adım bitmeden uygulama tam çalışmaz.
