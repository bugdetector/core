-Programı yüklemek için ilk önce verilen SQL dosyası MYSQL ile içe aktarın. İçe aktarılacak tablonun utf8_general_ci ile kodlanmış olması gerekiyor. 
-Tablo oluşturmak için aşağıdaki komutu kullanabilirsiniz.
 CREATE DATABASE core_multisite CHARACTER SET utf8 COLLATE utf8_general_ci;

-Veri tabanını kullanmak için sadece localhost üzerinden bağlanabilen özel bir mysql kullanıcısı oluşturun.
 create user core_multisite_user@localhost;
MYSQL 8.0 : CREATE USER core_multisite_user@localhost IDENTIFIED WITH mysql_native_password BY "core_multisite1234";

-Bu kullanıcıya sadece bu veritabanına tam erişim yetkisi verin ve kesinlikle güçlü bir şifre kullanın.
 GRANT ALL PRIVILEGES ON core_multisite.* To 'core_multisite_user'@'localhost' IDENTIFIED BY 'core_multisite1234';
MYSQL 8.0: GRANT ALL PRIVILEGES ON core_multisite.* To 'core_multisite_user'@'localhost' WITH GRANT OPTION

-Daha sonra "config_example.php" dosyasını kopyalayın üzerinden veritabanı bağlantısı için gerekli olan bilgiler girin.
-Sitenin HTTP ya da HTTPS kulandığı bildirin.
-SITE_NAME kısmına sitenin adını yazın
-E-posta ayarlarını bildirin.
-EMAIL_USERNAME, sistem e-posta gönderdiği zaman görülecek olan kullanıcı adıdır.
-PAGE_SIZE_LIMIT, tablolar ekranında sayfalama yapılırken gösterilecek bir sayfa boyutunu gösterir.
-TIMEZONE, varsayılan zaman dilimidir.
-LANGUAGE, varsayılan dildir.
 Ayarları yaptıktan sonra siteye gidin ve kurulumu gerçekleştirin.
Varsayılan kullanıcı adı: root şifre: 1234