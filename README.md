## Tablo oluşturma işlemleri

 1. Tablo oluşturmak için aşağıdaki komutu kullanabilirsiniz.

> CREATE DATABASE core CHARACTER SET utf8 COLLATE utf8_general_ci;

  2. Veri tabanını kullanmak için sadece localhost üzerinden bağlanabilen özel bir mysql kullanıcısı oluşturun.
    
   

> MYSQL 5.0 : create user core_user@localhost;
> MYSQL 8.0 : CREATE USER core_user@localhost IDENTIFIED WITH mysql_native_password BY "core_1234";

    
      
    
  3. Bu kullanıcıya sadece bu veritabanına tam erişim yetkisi verin ve kesinlikle güçlü bir şifre kullanın.
    
    

> MYSQL 5.0: GRANT ALL PRIVILEGES ON core_multisite.* To 'core_user'@'localhost' IDENTIFIED BY  'core_1234';
> MYSQL 8.0: GRANT ALL PRIVILEGES ON core.* To 'core_user'@'localhost' WITH GRANT OPTION

    
      
  ## Yapılandırma işlemleri
   1. Siteye erişerek yükleme ekranından veritabanı bağlantı bilgisi ve yeni kullanıcı oluşturulması için adımları izleyin.
   
   Ya da config/ dizini altında bulunan config_example.php dosyasını kopyalayın ve veri tabanı bağlantı bilgilerini girin.
   Daha sonra `php bin/console config:export` komutunu çalıştırın.
   Oturum açabilmek için aşağıdaki komutla bir yönetici kullanıcı oluşturabilirsiniz.
  `php bin/console.php user:add-admin root bakiyucel38@gmail.com "Murat Baki" 1234`
    
   2. .htaccess_example dosyasını .htaccess olarak kopyalayın ve istediğiniz şekilde düzenleyin.

    
   4. Siteye tarayıcı üzerinden erişin ve yüklemeyi tamamlayın.
   
     
   ## Önemli Kontroller
   Dosya yükleme işlemleri için "files" dizinine yazma izni
   verildiğinden emin olun.
   
   Sadece geliştirme ortamları için, canlı ortamlar için değil,
   "config/" dizinine yazma ve dosya
   oluşturma izinlerini verin.

  ## Bağımlılıklar
  PHP v > 7.4

  php-common
  php-intl
  php-mbstring
  php-mysql

   5. Kod standardı kontrolü
  phpcs --standard=PSR12 <file>
  phpcbf --standard=PSR12 <file>