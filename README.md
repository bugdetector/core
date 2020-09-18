## Tablo oluşturma işlemleri

 1. Tablo oluşturmak için aşağıdaki komutu kullanabilirsiniz.

> CREATE DATABASE core CHARACTER SET utf8 COLLATE utf8_general_ci;

  2. Veri tabanını kullanmak için sadece localhost üzerinden bağlanabilen özel bir mysql kullanıcısı oluşturun.
    
   

> MYSQL 5.0 : create user core_multisite_user@localhost;
> MYSQL 8.0 : CREATE USER core_multisite_user@localhost IDENTIFIED WITH mysql_native_password BY "core_multisite1234";

    
      
    
  3. Bu kullanıcıya sadece bu veritabanına tam erişim yetkisi verin ve kesinlikle güçlü bir şifre kullanın.
    
    

> MYSQL 5.0: GRANT ALL PRIVILEGES ON core_multisite.* To 'core_multisite_user'@'localhost' IDENTIFIED BY  'core_multisite1234';
> MYSQL 8.0: GRANT ALL PRIVILEGES ON core.* To 'core_multisite_user'@'localhost' WITH GRANT OPTION

    
      
  ## Yapılandırma işlemleri
   1. "config_example.php" dosyasını kopyalayın üzerinden veritabanı bağlantısı için gerekli olan bilgiler girin.

    
   2. .htaccess_example dosyasını da .htaccess olarak kopyalayın ve istediğiniz şekilde düzenleyin.

> Sitenin HTTP ya da HTTPS kulandığı bildirin.
> E-posta ayarlarını bildirin.
> EMAIL_USERNAME, sistem e-posta gönderdiği zaman görülecek olan kullanıcı adıdır.
>TIMEZONE, varsayılan zaman dilimidir.
> LANGUAGE, varsayılan sistem dilidir.

    
   4. Siteye tarayıcı üzerinden erişin ve yüklemeyi tamamlayın.
    
    
   Varsayılan kullanıcı adı: root şifre: 1234
   
     
   ## Önemli Kontroller
   Dosya yükleme işlemleri için "files" dizinine yazma izni
   verildiğinden emin olun.
   
   Sadece geliştirme ortamları için, canlı ortamlar için değil,
   "kernel/migrations" ve "translations" dizinlerine yazma ve dosya
   oluşturma izinlerini verin.

  ## Bağımlılıklar
  PHP v > 7.4

  php-common
  php-intl
  php-mbstring
  php-mysql