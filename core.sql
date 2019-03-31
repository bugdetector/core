-- MySQL dump 10.13  Distrib 5.7.24, for Linux (x86_64)
--
-- Host: localhost    Database: core_multisite
-- ------------------------------------------------------
-- Server version	5.7.24-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `BLOCKED_IPS`
--

DROP TABLE IF EXISTS `BLOCKED_IPS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BLOCKED_IPS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IP` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ip` (`IP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BLOCKED_IPS`
--

LOCK TABLES `BLOCKED_IPS` WRITE;
/*!40000 ALTER TABLE `BLOCKED_IPS` DISABLE KEYS */;
/*!40000 ALTER TABLE `BLOCKED_IPS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EMAILS`
--

DROP TABLE IF EXISTS `EMAILS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `EMAILS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `KEY` varchar(255) DEFAULT NULL,
  `EN` longtext,
  `TR` longtext,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `key` (`KEY`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EMAILS`
--

LOCK TABLES `EMAILS` WRITE;
/*!40000 ALTER TABLE `EMAILS` DISABLE KEYS */;
INSERT INTO `EMAILS` VALUES (1,'password_reset','&#60;p&#62;You can reset your password using the link below.&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;/p&#62;','&#60;p&#62;Aşağıdaki bağlantıyı kullanarak şifrenizi sıfırlayabilirsiniz.&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;/p&#62;'),(2,'user_insert','&#60;p&#62;Your e-mail has defined as an admin account on %s. Before login you must set a password for your account. You can set a password using link below.&#60;/p&#62;&#60;p&#62;Your username : %s&#60;br&#62;&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;br&#62;&#60;/p&#62;','&#60;p&#62;E-postanız, %s üzerinde yönetici yetkisi olan bir hesap için tanımlandı. Oturum açmadan önce bir şifre belirlemeniz gerekiyor. Şifre belirleme işlemi için aşağıdaki bağlantıyı kullanabilirsiniz.&#60;/p&#62;&#60;p&#62;Kullanıcı adınız : %s&#60;br&#62;&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;br&#62;&#60;/p&#62;');
/*!40000 ALTER TABLE `EMAILS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LOGINS`
--

DROP TABLE IF EXISTS `LOGINS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LOGINS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LOGIN_DATE` datetime NOT NULL,
  `IP_ADRESS` varchar(16) NOT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `USER_ID` (`USER_ID`),
  CONSTRAINT `LOGINS_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `USERS` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LOGINS`
--

LOCK TABLES `LOGINS` WRITE;
/*!40000 ALTER TABLE `LOGINS` DISABLE KEYS */;
/*!40000 ALTER TABLE `LOGINS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RESET_PASSWORD_QUEUE`
--

DROP TABLE IF EXISTS `RESET_PASSWORD_QUEUE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RESET_PASSWORD_QUEUE` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER` int(11) DEFAULT NULL,
  `KEY` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `USER` (`USER`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RESET_PASSWORD_QUEUE`
--

LOCK TABLES `RESET_PASSWORD_QUEUE` WRITE;
/*!40000 ALTER TABLE `RESET_PASSWORD_QUEUE` DISABLE KEYS */;
/*!40000 ALTER TABLE `RESET_PASSWORD_QUEUE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ROLES`
--

DROP TABLE IF EXISTS `ROLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ROLES` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ROLE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `role` (`ROLE`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ROLES`
--

LOCK TABLES `ROLES` WRITE;
/*!40000 ALTER TABLE `ROLES` DISABLE KEYS */;
INSERT INTO `ROLES` VALUES (1,'ADMIN'),(2,'USER');
/*!40000 ALTER TABLE `ROLES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TRANSLATIONS`
--

DROP TABLE IF EXISTS `TRANSLATIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TRANSLATIONS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `EN` varchar(255) DEFAULT NULL,
  `TR` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `EN` (`EN`),
  UNIQUE KEY `TR` (`TR`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TRANSLATIONS`
--

LOCK TABLES `TRANSLATIONS` WRITE;
/*!40000 ALTER TABLE `TRANSLATIONS` DISABLE KEYS */;
INSERT INTO `TRANSLATIONS` VALUES (1,'Tables','Tablolar'),(2,'Management','Yönetim'),(3,'Manual','Kılavuz'),(4,'Logout','Oturumu Kapat'),(5,'User Management','Kullanıcı Yönetimi'),(6,'Role Management','Rol Yönetimi'),(7,'Sessions of User','Kullanıcının Oturumları'),(8,'Add User','Kullanıcı Ekle'),(9,'Edit User','Kullanıcıyı düzenle'),(10,'Remove User','Kullanıcıyı Sil'),(11,'Add Role','Rol Ekle'),(12,'Remove Role','Rolü Sil'),(13,'New Table','Yeni Tablo'),(14,'Add','Ekle'),(15,'New Column','Yeni Kolon'),(16,'Remove Table','Tabloyu Kaldır'),(17,'Options','Seçenekler'),(18,'Roles of %s','%s kullanıcısının rolleri'),(19,'This user not exists.','Bu kullanıcı mevcut değil.'),(20,'Username','Kullanıcı adı'),(21,'Login','Oturum aç'),(22,'Password','Şifre'),(23,'Forget Password','Şifremi Unuttum'),(24,'Wrong username or password.','Kullanıcı adı ya da şifre yanlış.'),(25,'Too many login fails. Please try again 10 minutes later.','Çok fazla hatalı giriş yaptınız. 10 dk sonra tekrar deneyiniz.'),(26,'Please be sure that you written %s correct.','%s alanını doğru yazdığınızdan emin olun.'),(27,'Name','İsim'),(28,'surname','soyisim'),(29,'phone','telefon numarası'),(30,'Please enter a valid e-mail address.','Geçerli bir e-posta adresi giriniz.'),(31,'Invalid role.','Geçersiz rol belirteci.'),(32,'Updated successfuly.','Başarıyla güncellendi.'),(33,'User Information','Kullanıcı Bilgileri'),(34,'Last access','Son erişim'),(35,'E-mail','E-posta'),(36,'Roles','Roller'),(37,'Save','Kaydet'),(38,'Update Password','Şifre Güncelle'),(39,'Current Password','Geçerli Şifre'),(40,'Password (again)','Şifre (tekrar)'),(41,'This email address is currently used.','Bu e-posta adresi kullanılmaktadır.'),(42,'This username is used. ','Bu kullanıcı adı kullanılmaktadır.'),(43,'E-mail could not be sent.','E-posta gönderilemedi.'),(44,'Username can only contain letters and numbers and must contain at least %d characters. ','Kullanıcı adı sadece harf ve rakam içerebilir ve en az %d karakter içermelidir.'),(45,'You must specify a role to this user.','Bu kullanıcı için bir rol belirtmelisiniz.'),(46,'Please be sure that you written password correct. ','Şifreyi doğru yazdığınızdan emin olun.'),(47,'The password must be at least 8 characters long and must contain uppercase letter, lowercase letter, punctioation and number. ','Şifre en az 8 karakter uzunluğunda olmalı. Büyük harf, küçük harf, noktalama işareti ve rakam bulundurmalı. .'),(48,'Created Date','Oluşturulma Tarihi'),(49,'Role','Rol'),(50,'Role Name','Rol Adı'),(51,'Select a table to make a transaction.','İşlem yapmak için bir tablo seçin.'),(52,'Info: ','Bilgi: '),(53,'Error!','Hata!'),(54,'Warning!','Uyarı!'),(55,'Search','Arama'),(56,'Available characters: %s','Kullanılabilir karakterler: %s'),(57,'Column Name','Kolon Adı'),(58,'Data Type','Veri Tipi'),(59,'Unique','Benzersiz'),(60,'Table Name','Tablo Adı'),(61,'New Field','Yeni Alan'),(62,'Length (max 255):','Uzunluk (en büyük 255):'),(63,'Reference Table:','İlişkili Tablo:'),(64,'Back to mainpage.','Anasayfaya geri dön.'),(65,'Document removed.','Döküman silindi.'),(66,'A table with same name already created.','Bu isimde bir tablo zaten tanımlı.'),(67,'Invalid operation.','Geçersiz işlem.'),(68,'Table created successfully.','Tablo başarıyla oluşturuldu.'),(69,'%s table deleted.','%s tablosu silindi.'),(70,'%s deleted.','%s silindi.'),(71,'There is at least one user which is in this role.','Bu rolü kullanan en az bir kullanıcı mevcut.'),(72,'Role removed.','Rol silindi.'),(73,'Password Reset','Şifre Sıfırlama'),(74,'Wrong username or e-mail.','Kullanıcı adı ya da e-posta yanlış.'),(75,'Reset','Sıfırla'),(76,'Cancel','Vazgeç'),(77,'OK','Tamam'),(78,'%s role defined.','%s rol tanımı yapıldı.'),(79,'Nothing chosen.','Hiçbir şey seçilmedi.'),(80,'Please check wrong fields.','Lütfen hatalı alanları kontrol edin.'),(81,'Are you sure to remove that record?','Bu kaydı silmek istediğinize emin misiniz?'),(82,'Delete','Sil'),(83,'Browse','Gözat'),(84,'Clean','Temizle'),(85,'Update','Güncelle'),(86,'Password reset successfully.','Şifre başarıyla sıfırlandı.'),(87,'This link maybe used before.','Bu bağlantı daha önce kullanılmış olabilir.'),(88,'Password reset e-mail has send.','Şifre sıfırlama e-postası gönderildi.'),(89,'Sorry','Maalesef'),(90,'Page Not Found','Sayfa Bulunamadı.'),(91,'Record inserted succesfully.','Kayıt başarıyla eklendi.'),(92,'%s user definition','%s kullanıcı tanımı'),(93,'Are you sure want to drop table %s?','%s tablosunu kaldırmak istediğinize emin misiniz?'),(94,'Out of %d records %d-%d is monitoring.','Toplan %d kayıttan %d-%d arası görüntüleniyor.'),(95,'Invalid key.','Geçersiz anahtar.'),(96,'Due to untrusted transactions, your IP has been blocked.','Güvenilmez istekler nedeniyle IP adresiniz engellenmiştir.'),(97,'Your user account has been blocked. Please login after reset password.','Hesabınız kilitlenmiştir. Lütfen şifrenizi sıfırlayarak sisteme yeniden giriş yapın.'),(98,'Are you sure want to remove user %s?','%s kullanıcısını silmek istediğinize emin misiniz?'),(99,'File cannot uploaded.','Dosya yüklenemedi.'),(100,'Translations','Çeviri'),(101,'Import','İçe aktar'),(102,'Export','Dışa aktar'),(103,'This option only imports translations on translations/translations.json file.','Bu seçenek sadece translations/translations.json dosyasındaki çevirileri içe aktarır.'),(104,'This will exports translations on translations/translations.json file.','Bu seçenek çevirileri, translations/translations.json dosyasına dışa aktarır.'),(105,'An error occured.','Bir hata oluştu.'),(106,'Exported succesfully.','Başarıyla dışarı aktarıldı.'),(107,'Imported successfully.','Başarıyla içe aktarıldı.');
/*!40000 ALTER TABLE `TRANSLATIONS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `USERS`
--

DROP TABLE IF EXISTS `USERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `USERS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USERNAME` varchar(20) DEFAULT NULL,
  `NAME` varchar(50) NOT NULL,
  `SURNAME` varchar(50) NOT NULL,
  `EMAIL` varchar(50) NOT NULL,
  `PHONE` varchar(10) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `CREATED_AT` datetime DEFAULT NULL,
  `ACCESS` datetime DEFAULT NULL,
  `STATUS` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `EMAIL` (`EMAIL`),
  UNIQUE KEY `USERNAME` (`USERNAME`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `USERS`
--

LOCK TABLES `USERS` WRITE;
/*!40000 ALTER TABLE `USERS` DISABLE KEYS */;
INSERT INTO `USERS` VALUES (1,'root','Murat Baki','Yücel','bakiyucel38@gmail.com','5079158686','03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4','2018-04-06 13:45:00','2019-03-31 12:52:23','active'),(2,'guest','','','','','','2018-04-06 13:45:00','2018-12-31 11:38:23','active');
/*!40000 ALTER TABLE `USERS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `USERS_ROLES`
--

DROP TABLE IF EXISTS `USERS_ROLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `USERS_ROLES` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) DEFAULT NULL,
  `ROLE_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `user_id` (`USER_ID`),
  KEY `role_id` (`ROLE_ID`),
  CONSTRAINT `USERS_ROLES_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `USERS` (`ID`),
  CONSTRAINT `USERS_ROLES_ibfk_2` FOREIGN KEY (`ROLE_ID`) REFERENCES `ROLES` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `USERS_ROLES`
--

LOCK TABLES `USERS_ROLES` WRITE;
/*!40000 ALTER TABLE `USERS_ROLES` DISABLE KEYS */;
INSERT INTO `USERS_ROLES` VALUES (1,1,1);
/*!40000 ALTER TABLE `USERS_ROLES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `WATCHDOG`
--

DROP TABLE IF EXISTS `WATCHDOG`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `WATCHDOG` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `EVENT` varchar(255) DEFAULT NULL,
  `VALUE` varchar(255) DEFAULT NULL,
  `DATE` datetime DEFAULT NULL,
  `IP` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `WATCHDOG`
--

LOCK TABLES `WATCHDOG` WRITE;
/*!40000 ALTER TABLE `WATCHDOG` DISABLE KEYS */;
/*!40000 ALTER TABLE `WATCHDOG` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `available_sites`
--

DROP TABLE IF EXISTS `available_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `available_sites` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `available_sites`
--

LOCK TABLES `available_sites` WRITE;
/*!40000 ALTER TABLE `available_sites` DISABLE KEYS */;
INSERT INTO `available_sites` VALUES (1,'maverahukuk.com'),(2,'arslantashukuk.com');
/*!40000 ALTER TABLE `available_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cdn`
--

DROP TABLE IF EXISTS `cdn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cdn` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `file` tinytext,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cdn`
--

LOCK TABLES `cdn` WRITE;
/*!40000 ALTER TABLE `cdn` DISABLE KEYS */;
/*!40000 ALTER TABLE `cdn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `title` text,
  `body` longtext,
  `site_name` int(11) DEFAULT NULL,
  `url_alias` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `site_name` (`site_name`),
  CONSTRAINT `content_ibfk_1` FOREIGN KEY (`site_name`) REFERENCES `available_sites` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content`
--

LOCK TABLES `content` WRITE;
/*!40000 ALTER TABLE `content` DISABLE KEYS */;
/*!40000 ALTER TABLE `content` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-03-31 14:07:29
