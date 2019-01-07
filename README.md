-Programı yüklemek için ilk önce verilen SQL dosyası MYSQL ile içe aktarın. İçe aktarılacak tablonun utf8_general_ci ile kodlanmış olması gerekiyor. 
-Tablo oluşturmak için aşağıdaki komutu kullanabilirsiniz.
 CREATE DATABASE core CHARACTER SET utf8 COLLATE utf8_general_ci;

-Veri tabanını kullanmak için sadece localhost üzerinden bağlanabilen özel bir mysql kullanıcısı oluşturun.
 create user core_user@localhost;

-Bu kullanıcıya sadece bu veritabanına tam erişim yetkisi verin ve kesinlikle güçlü bir şifre kullanın.
 GRANT ALL PRIVILEGES ON core.* To 'core_user'@'localhost' IDENTIFIED BY 'core1234';

-Daha sonra "core-config.php" dosyası üzerinden veritabanı bağlantısı için gerekli olan bilgiler girin.
-Sitenin HTTP ya da HTTPS kulandığı bildirin.
-Sitenin sunucu üzerinde kurulduğu dizini SITE_ROOT ile bildirin.
-E-posta ayarlarını bildirin.
-EMAIL_USERNAME, sistem e-posta gönderdiği zaman görülecek olan kullanıcı adıdır.
-PAGE_SIZE_LIMIT, tablolar ekranında sayfalama yapılırken gösterilecek bir sayfa boyutunu gösterir.
-TIMEZONE, varsayılan zaman dilimidir.
-LANGUAGE, varsayılan dildir.
