# Adım 6 — Dokümantasyon: `AGENTS.md` ve `README.md`

## Amaç

Yeni env ve Docker yapısını belgelemek, `.github/copilot-instructions.md` içeriğini `AGENTS.md`'ye taşımak
ve README'yi yeni kurulum akışına göre İngilizce olarak yeniden yazmak.

## Değişen dosyalar

| Dosya | Değişiklik |
|---|---|
| `AGENTS.md` (yeni) | AI agent'lar ve geliştiriciler için tek kaynak. En başta kurallar, env yapısı, Docker ve kurulum; ardından copilot dosyasından taşınan framework bilgileri. |
| `.github/copilot-instructions.md` (silindi) | İçeriği `AGENTS.md`'ye taşındı. GitHub Copilot da `AGENTS.md`'yi okuyabiliyor. `.github/` klasörü boşaldığı için o da kalktı. |
| `README.md` | Türkçe kurulum notları yerine İngilizce: Docker ile hızlı başlangıç, konfigürasyon, production, geliştirme akışı, Docker'sız kurulum. |

## `AGENTS.md`'nin yapısı

1. **Ground rules:** PHP 8.4, komutların container'da çalıştırılması, PSR-12, gizli bilgilerin commit'lenmemesi, `config:export`.
2. **Configuration:** env dosyaları, yükleme sırası, `Environment` sınıfı, `APP_ENV` değerleri, `HASH_SALT`, değer biçimleri,
   yeni bir ayarın nasıl ekleneceği.
3. **Docker:** dosyaların görevleri, portlar, Compose'un `.env.local`'i okumaması.
4. **Installation and database:** web kurulum ekranının olmadığı ve entrypoint'in ne zaman `config:import` çalıştırdığı.
5. **Copilot dosyasından taşınanlar:** mimari, controller, entity/ORM, formlar, temalar, çeviriler, komutlar, iş akışları, güvenlik.

## Taşırken düzeltilen bilgiler

Copilot dosyasındaki bazı bilgiler yanlıştı ya da eskimişti; kodla karşılaştırılıp düzeltildi:

| Copilot dosyasında | Gerçekte |
|---|---|
| `php bin/console.php serve` | Komutun adı `cdb:serve`. Docker ile gerek yok. |
| Kökte `Src/` klasörü | Yolu `Kernel/Src/`. |
| Komut listesi | `notifications:send`, `notifications:test`, `schedule:run` eksikti. |
| `clear:cache` "sistem cache'ini temizler" | Cache tablosunu **ve** Twig cache klasörünü temizler. |
| `clear:temporary-files` "geçici dosyaları temizler" | Sadece 1 saatten eski geçici upload'ları siler. |
| `config/config.php` → `ENVIRONMENT` | Böyle bir sabit hiç yoktu (kodda `ENVIROMENT` geçiyordu); artık `APP_ENV` env değişkeni var. |
| Deploy: `rm -r cache/` | Twig cache container'ın içinde; her yeni image boş bir cache ile başlıyor. |

## README'de öne çıkanlar

- **Hızlı başlangıç:** tek bir `cp` ve `docker compose up`; adresler ve giriş bilgileri tablo hâlinde.
- **Production adımları:** `.env.prod.local` örneği, host MySQL'de kullanıcı oluşturma (`'core'@'172.%'`, çünkü container
  `localhost`'tan değil Docker ağından bağlanır), `bind-address`, MySQL'e timezone tablolarının yüklenmesi
  (`TIMEZONE=Europe/Istanbul` isimli zaman dilimi kullanıyor), ilk admin, `schedule:run` için cron, Firebase dosyasının mount edilmesi.
- **Port değiştirme tuzağı:** `APP_PORT` değişirse `TRUSTED_HOSTS` da değişmeli. `CoreDB::baseHost()` güvenilmeyen
  bir host gördüğünde listedeki ilk host'a döner; bu yüzden linkler eski porta gider.
- **Eski README'deki bilgiler korundu:** DB oluşturma, bağımlılıklar (PHP 7.4 → 8.4 olarak güncellendi), PSR-12 kontrolü, commit öncesi `config:export`.

## Doğrulama

- Dokümanlardaki komutlar container'da denendi: `bin/console.php list` komut adları, `vendor/bin/phpcs` (`App/` temiz), `vendor/bin/phpcbf` mevcut.
- Docker ağının alt ağı `172.19.0.0/16`; README'deki `172.%` örneği bunu kapsıyor.
- Kalan `config.php` referansları bilerek bırakıldı: `.gitignore` ve `.dockerignore` (eski kurulumlardaki gizli dosyalar
  commit'lenmesin ve image'a girmesin diye), README ve AGENTS.md ("artık yok" açıklaması).
