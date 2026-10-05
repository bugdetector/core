# Adım 5 — Docker kurulumu ve doğrulama

## Amaç

Repoyu klonlayan biri tek bir kopyalama ve `docker compose up` ile çalışan bir CoreDB elde etsin.
Production aynı image'ı kullansın ama makinedeki (host) MySQL'e bağlansın.
(Plandaki 6. adım, yani doğrulama, bu adıma dahil edildi: test sırasında çıkan düzeltmeler aynı commit'e girsin diye.)

## Yeni dosyalar

| Dosya | Görevi |
|---|---|
| `Dockerfile` | `base` → `dev` / `prod` hedefleri (multi-stage) |
| `.dockerignore` | `.git`, `vendor`, upload'lar, `docs` ve **gizli dosyalar** image'a girmez |
| `compose.yml` | Her ortamda ortak olan `app` servisi ve port (`APP_PORT`, varsayılan 8000) |
| `compose.override.example.yml` | Yerel ortam: kodun bind mount'u, MySQL 8.4, phpMyAdmin, ilk admin bilgileri |
| `compose.prod.yml` | Production: `prod` hedefi, `APP_ENV=prod`, host MySQL erişimi, `.env.prod.local` mount'u, upload volume'u |
| `docker/entrypoint.sh` | Container açılışında: `vendor` yoksa kur → DB'yi bekle → DB boşsa kur ve admin oluştur → Apache'yi başlat |
| `docker/db-status.php` | Entrypoint için DB durumunu yazar: `installed` / `empty` / ulaşılamıyor (çıkış kodu 1) |
| `docker/apache/vhost.conf` | Document root `public_html`; `.htaccess` yoksa `.htaccess_example` kullanılır |
| `docker/php/app.ini` | Eskiden `.htaccess_example` içinde olan PHP limitleri |

## Kullanım

```bash
# Yerel ortam (ilk seferde bir kez kopyala)
cp compose.override.example.yml compose.override.yml
docker compose up
# Uygulama: http://localhost:8000  (admin / admin)
# phpMyAdmin: http://localhost:8080  (core / core)
# MySQL host'tan: 127.0.0.1:3307

# Production (önce .env.prod.local oluşturulmalı)
docker compose -f compose.yml -f compose.prod.yml up -d --build
```

Production için `.env.prod.local` dosyasına en az şunlar yazılmalı:

```dotenv
HASH_SALT=<php -r 'echo bin2hex(random_bytes(32));' çıktısı>
DB_SERVER=host.docker.internal
DB_NAME=...
DB_USER=...
DB_PASSWORD=...
```

## Tasarım kararları

| Karar | Neden |
|---|---|
| **PHP tam olarak 8.4** | `Pdo\Mysql` sınıfı ≥ 8.4 istiyor, `kreait/firebase-php` ≤ 8.4'e izin veriyor. |
| **Eklentiler:** `pdo_mysql gd exif gmp bcmath intl opcache zip` | Kodda kullanılan fonksiyonlara göre seçildi: resim işleme (gd, exif), VAPID şifreleme (gmp, bcmath), composer (zip). |
| **`config:import` sadece DB boşsa çalışıyor** | Komut tablo yapısını yml'ye göre değiştiriyor. Her açılışta çalışsaydı admin panelinden yapılıp henüz export edilmemiş tablo değişikliklerini bozabilirdi. |
| **Admin bilgileri sadece `compose.override.example.yml`'de** | `.env` production image'ına da giriyor. `admin/admin` orada olsaydı boş bir production DB'sinde bu şifreyle admin oluşurdu. Production'da admin elle oluşturulur: `docker compose exec app php bin/console.php user:add-admin ...` |
| **`AccessFileName .htaccess .htaccess_example`** | Apache listedeki **ilk var olan** dosyayı okur. Dev'de bind mount image'daki dosyaları örttüğü için `.htaccess`'i image'a kopyalamak işe yaramazdı. Bu ayarla elle kopyalamaya gerek kalmıyor, ama isteyen yine kendi `.htaccess` dosyasını oluşturabiliyor. |
| **`.htaccess_example`'daki `php7_module` bloğu → `docker/php/app.ini`** | PHP 8'de modülün adı `php_module`. O blok hiç çalışmıyordu. |
| **Prod'da `.env.prod.local` dosya olarak mount ediliyor (`env_file` değil)** | Dosya, dev'deki gibi Symfony Dotenv ile okunuyor; iki farklı ayrıştırıcı arasında tırnak veya `$` farkı yaşanmıyor. `create_host_path: false` sayesinde dosya yoksa Docker sessizce boş bir klasör oluşturmak yerine hata veriyor. |
| **MySQL host'ta 3307 portunda** | Makinede kurulu bir MySQL (ör. Homebrew) zaten 3306'yı kullanıyor olabilir; bu makinede de öyleydi. |
| **Composer katmanı koddan önce kopyalanıyor** | `composer.json`/`composer.lock` değişmedikçe `vendor` katmanı cache'ten geliyor; sadece kod değişince build hızlı. |

## Öğrendiklerimiz / dikkat edilecekler

1. **Docker Compose `.env.local`'i okumaz.** Compose, `${DB_NAME}` gibi ifadeleri sadece `.env`'den doldurur.
   Yerel MySQL'in DB bilgileri `.env.local`'de değiştirilirse PHP yeni değeri görür ama MySQL container'ı görmez.
2. **Konfigürasyon hatası ile "DB henüz hazır değil" ayrı ele alınmalı.** İlk sürümde entrypoint her hatayı "DB bekleniyor" diye yorumluyordu.
   Yanlış bir `HASH_SALT` 60 saniye boyunca "Waiting for the database..." logu olarak görünüyordu.
   Şimdi `db-status.php` sadece `PDOException` için 1 ile çıkıyor; diğer hatalar beklemeden gösteriliyor.
3. **`host.docker.internal`.** Container'ın içinde `localhost` container'ın kendisidir. Makinedeki MySQL'e
   `host.docker.internal` ile ulaşılır. Linux'ta bunun için `extra_hosts: host-gateway` gerekir.
   Host'taki MySQL'in container'dan gelen bağlantıyı kabul etmesi gerekir: `bind-address` ve kullanıcının
   `'core'@'localhost'` değil, örneğin `'core'@'%'` ya da Docker ağı için tanımlanmış olması.
4. **Kapsam dışı, mevcut bir sorun.** `Kernel/Src/Entity/Translation.php:43` CLI'da `explode(null)` deprecation uyarısı veriyor,
   çünkü `HTTP_ACCEPT_LANGUAGE` yok. Dev'de `display_errors` açık olduğu için entrypoint loglarında görünüyor.

## Doğrulama

**Yerel (`docker compose up`):**
- MySQL healthy olunca app açıldı; boş DB algılandı → `config:import` → admin oluşturuldu → Apache başladı.
- `/` 200, `/login` 200, `/admin` 302 (giriş yok), `/olmayan-sayfa` 404, `/install` 404, `robots.txt` 200.
- `/.htaccess_example` ve `/files/.htaccess` 403. Routing `.htaccess_example` üzerinden çalışıyor.
- `/manifest.json` env'deki PWA değerleriyle oluşuyor (`"#fff"` dahil).
- Login formuyla `admin/admin` girişi → 302 `/admin/` → admin sayfası açıldı.
- phpMyAdmin (8080) 200.
- Yeniden başlatmada kurulum **tekrar çalışmadı** (1 kullanıcı, 21 tablo).

**Production (`compose.prod.yml`):**
- `.env.prod.local` yok → compose açık bir hata verdi, boş klasör oluşmadı.
- Salt `.env`'deki değer → `HASH_SALT still has the public value` hatasıyla açılmadı.
- Gizli bir salt ile → açıldı; `/` 200, `/login` 200, 404 sayfası çalışıyor, `/.env` erişilemez.
- `display_errors` kapalı, `APP_ENV=prod`, Twig cache yazılabiliyor, `host.docker.internal` çözülüyor.
- Image'ın içinde `.env.*.local`, `config.php` ya da Firebase JSON yok; sadece `.env` blueprint'i var.
- Ulaşılamayan DB (`DB_SERVER=db-yok`) → "Waiting for the database..." ile tekrar deniyor.
