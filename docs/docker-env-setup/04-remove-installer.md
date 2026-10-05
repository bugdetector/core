# Adım 4 — Web kurulum ekranının (`/install`) kaldırılması

## Amaç

Kurulum ekranının asıl işi `config/config.php` dosyasını diske yazmaktı. Env modelinde bu işe gerek kalmadı.
Ekranın geri kalan işleri (tabloları kurmak, çevirileri yüklemek, admin kullanıcı oluşturmak) CLI'da zaten vardı.
Ekranı kaldırınca bir güvenlik açığı da kapanıyor: DB kurulmamışken `/install` sayfası **herkese** açıktı,
yani yeni bir production kurulumunda siteye ilk giren kişi admin hesabını oluşturabiliyordu.

## Silinen dosyalar

| Dosya | Ne yapıyordu |
|---|---|
| `Kernel/Src/Controller/InstallController.php` | `/install` sayfası ve erişim kontrolü |
| `Kernel/Src/Form/InstallForm.php` | DB bilgileri + admin formu; `config.php` yazıyor, tabloları kuruyor, admin oluşturuyordu |
| `Kernel/Src/BaseTheme/templates/forms/install-form.twig` | Formun şablonu |
| `config/config_example.php` | `config.php` şablonu; yerini `.env` aldı |

## Değişen dosyalar

| Dosya | Değişiklik |
|---|---|
| `bootstrap.php` | `CONFIGURATON_LOADED` sabiti ve `/install` yönlendirmesi kaldırıldı. DB kurulmamışsa artık 503 dönüyor ve `config:import` komutunu öneriyor. |
| `config/translations/en.yml`, `tr.yml` | Sadece kurulum ekranının kullandığı 12 çeviri anahtarı silindi (`install_*`, `db_*`, `config_file_write_error`, `user_details`, `cant_connect_to_database`, `all_configuration_imported`, `install`). |
| `config/table_structure/variables.yml` + çeviriler | Tablo açıklamasındaki "For more security use config.php" ifadesi, gizli bilgilerin env'de tutulması gerektiğini söyleyen bir ifadeyle değiştirildi. |

## Kurulum artık nasıl yapılıyor?

| Eski (web ekranı) | Yeni (CLI) |
|---|---|
| DB bilgilerini forma gir → `config.php` yazılır | DB bilgileri `.env` / `.env.local` dosyalarında |
| Rastgele `HASH_SALT` üretilir | `HASH_SALT` env'de (prod'da kendi değerin zorunlu) |
| Tablolar ve çeviriler kurulur | `php bin/console.php config:import` |
| Admin kullanıcı oluşturulur | `php bin/console.php user:add-admin <kullanıcı> <email> "<ad>" <şifre>` |

5. adımda bu komutlar Docker container'ının entrypoint script'inde otomatik çalışacak.

## Öğrendiklerimiz / dikkat edilecekler

1. **Tablo açıklaması aynı zamanda çeviri anahtarı.** Bu framework'te tablo açıklamasının İngilizce metni
   çeviri dosyalarında da anahtar olarak geçiyor. Açıklama değişince `table_structure/*.yml`, `en.yml` ve `tr.yml`
   birlikte güncellenmeli.
2. **Boş DB'de çeviriler yml'den okunuyor.** `Translation::getTranslation()` tablolar yoksa
   `DatabaseInstallationException` yakalayıp çevirileri `config/translations/*.yml` dosyalarından okuyor.
   Bu sayede `config:import` boş bir DB'de de çalışabiliyor.
3. **Eski DB'lerde kalanlar.** Mevcut kurulumların `translations` tablosunda silinen anahtarlar kalabilir. Zararsızlar.
   Ancak biri eski DB'den `config:export` çalıştırırsa bu anahtarlar yml'ye geri gelebilir.
4. **`hash_salt` değişkeni bilerek bırakıldı.** `config/table_dump_data/variables.yml` içindeki `hash_salt` kaydını
   sadece kurulum ekranı kullanıyordu; artık hiçbir yerde okunmuyor. Mevcut DB'leri etkilememek için bu adımda
   silinmedi; ayrı bir temizlik işi olarak ele alınabilir.

## Doğrulama

- `config.php`, `config_example`, `CONFIGURATON_LOADED`, `InstallController`, `InstallForm` için arama yapıldı.
  Kalan yerler `README.md` ve `.github/copilot-instructions.md` (7. adımda güncellenecek) ve
  `.gitignore`'daki `config/config.php` satırı (eski kurulumlardaki gizli bilgiler yanlışlıkla commit'lenmesin diye bilerek tutuluyor).
- `config/table_dump_data` ve menü verilerinde `/install`'a link yok.
- Değiştirilen üç YAML dosyası Symfony Yaml ile sorunsuz okunuyor (en/tr: 238 anahtar).
- `bootstrap.php` için `php -l` temiz. phpcs sonucu değişiklikten öncekiyle aynı (1 eski uyarı).
