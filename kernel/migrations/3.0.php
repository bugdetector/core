<?php

$sql = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = '+00:00';

CREATE TABLE `BLOCKED_IPS` (
  `ID` int(11) NOT NULL,
  `IP` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `EMAILS` (
  `ID` int(11) NOT NULL,
  `KEY` varchar(255) DEFAULT NULL,
  `EN` longtext,
  `TR` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `EMAILS` (`ID`, `KEY`, `EN`, `TR`) VALUES
(1, 'password_reset', '&#60;p&#62;You can reset your password using the link below.&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;/p&#62;', '&#60;p&#62;Aşağıdaki bağlantıyı kullanarak şifrenizi sıfırlayabilirsiniz.&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;/p&#62;'),
(2, 'user_insert', '&#60;p&#62;Your e-mail has defined as an admin account on %s. Before login you must set a password for your account. You can set a password using link below.&#60;/p&#62;&#60;p&#62;Your username : %s&#60;br&#62;&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;br&#62;&#60;/p&#62;', '&#60;p&#62;E-postanız, %s üzerinde yönetici yetkisi olan bir hesap için tanımlandı. Oturum açmadan önce bir şifre belirlemeniz gerekiyor. Şifre belirleme işlemi için aşağıdaki bağlantıyı kullanabilirsiniz.&#60;/p&#62;&#60;p&#62;Kullanıcı adınız : %s&#60;br&#62;&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;br&#62;&#60;/p&#62;');

CREATE TABLE `RESET_PASSWORD_QUEUE` (
  `ID` int(11) NOT NULL,
  `USER` int(11) DEFAULT NULL,
  `KEY` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ROLES` (
  `ID` int(11) NOT NULL,
  `ROLE` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ROLES` (`ID`, `ROLE`) VALUES
(1, 'ADMIN'),
(2, 'USER');

CREATE TABLE `TRANSLATIONS` (
  `ID` int(11) NOT NULL,
  `EN` varchar(255) DEFAULT NULL,
  `TR` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `TRANSLATIONS` (`ID`, `EN`, `TR`) VALUES
(1, 'Tables', 'Tablolar'),
(2, 'Management', 'Yönetim'),
(3, 'Manual', 'Kılavuz'),
(4, 'Logout', 'Oturumu Kapat'),
(5, 'User Management', 'Kullanıcı Yönetimi'),
(6, 'Role Management', 'Rol Yönetimi'),
(7, 'Sessions of User', 'Kullanıcının Oturumları'),
(8, 'Add User', 'Kullanıcı Ekle'),
(9, 'Edit User', 'Kullanıcıyı düzenle'),
(10, 'Remove User', 'Kullanıcıyı Sil'),
(11, 'Add Role', 'Rol Ekle'),
(12, 'Remove Role', 'Rolü Sil'),
(13, 'New Table', 'Yeni Tablo'),
(14, 'Add', 'Ekle'),
(15, 'New Column', 'Yeni Kolon'),
(16, 'Remove Table', 'Tabloyu Kaldır'),
(17, 'Options', 'Seçenekler'),
(18, 'Roles of %s', '%s kullanıcısının rolleri'),
(19, 'This user not exists.', 'Bu kullanıcı mevcut değil.'),
(20, 'Username', 'Kullanıcı adı'),
(21, 'Login', 'Oturum aç'),
(22, 'Password', 'Şifre'),
(23, 'Forget Password', 'Şifremi Unuttum'),
(24, 'Wrong username or password.', 'Kullanıcı adı ya da şifre yanlış.'),
(25, 'Too many login fails. Please try again 10 minutes later.', 'Çok fazla hatalı giriş yaptınız. 10 dk sonra tekrar deneyiniz.'),
(26, 'Please be sure that you written %s correct.', '%s alanını doğru yazdığınızdan emin olun.'),
(27, 'Name', 'İsim'),
(28, 'surname', 'soyisim'),
(29, 'phone', 'telefon numarası'),
(30, 'Please enter a valid e-mail address.', 'Geçerli bir e-posta adresi giriniz.'),
(31, 'Invalid role.', 'Geçersiz rol belirteci.'),
(32, 'Updated successfuly.', 'Başarıyla güncellendi.'),
(33, 'User Information', 'Kullanıcı Bilgileri'),
(34, 'Last access', 'Son erişim'),
(35, 'E-mail', 'E-posta'),
(36, 'Roles', 'Roller'),
(37, 'Save', 'Kaydet'),
(38, 'Update Password', 'Şifre Güncelle'),
(39, 'Current Password', 'Geçerli Şifre'),
(40, 'Password (again)', 'Şifre (tekrar)'),
(41, 'This email address is currently used.', 'Bu e-posta adresi kullanılmaktadır.'),
(42, 'This username is used. ', 'Bu kullanıcı adı kullanılmaktadır.'),
(43, 'E-mail could not be sent.', 'E-posta gönderilemedi.'),
(44, 'Username can only contain letters and numbers and must contain at least %d characters. ', 'Kullanıcı adı sadece harf ve rakam içerebilir ve en az %d karakter içermelidir.'),
(45, 'You must specify a role to this user.', 'Bu kullanıcı için bir rol belirtmelisiniz.'),
(46, 'Please be sure that you written password correct. ', 'Şifreyi doğru yazdığınızdan emin olun.'),
(47, 'The password must be at least 8 characters long and must contain uppercase letter, lowercase letter, punctioation and number. ', 'Şifre en az 8 karakter uzunluğunda olmalı. Büyük harf, küçük harf, noktalama işareti ve rakam bulundurmalı. .'),
(48, 'Created Date', 'Oluşturulma Tarihi'),
(49, 'Role', 'Rol'),
(50, 'Role Name', 'Rol Adı'),
(51, 'Select a table to make a transaction.', 'İşlem yapmak için bir tablo seçin.'),
(52, 'Info: ', 'Bilgi: '),
(53, 'Error!', 'Hata!'),
(54, 'Warning!', 'Uyarı!'),
(55, 'Search', 'Arama'),
(56, 'Available characters: %s', 'Kullanılabilir karakterler: %s'),
(57, 'Column Name', 'Kolon Adı'),
(58, 'Data Type', 'Veri Tipi'),
(59, 'Unique', 'Benzersiz'),
(60, 'Table Name', 'Tablo Adı'),
(61, 'New Field', 'Yeni Alan'),
(62, 'Length (max 255):', 'Uzunluk (en büyük 255):'),
(63, 'Reference Table:', 'İlişkili Tablo:'),
(64, 'Back to mainpage.', 'Anasayfaya geri dön.'),
(65, 'Document removed.', 'Döküman silindi.'),
(66, 'A table with same name already created.', 'Bu isimde bir tablo zaten tanımlı.'),
(67, 'Invalid operation.', 'Geçersiz işlem.'),
(68, 'Table created successfully.', 'Tablo başarıyla oluşturuldu.'),
(69, '%s table deleted.', '%s tablosu silindi.'),
(70, '%s deleted.', '%s silindi.'),
(71, 'There is at least one user which is in this role.', 'Bu rolü kullanan en az bir kullanıcı mevcut.'),
(72, 'Role removed.', 'Rol silindi.'),
(73, 'Password Reset', 'Şifre Sıfırlama'),
(74, 'Wrong username or e-mail.', 'Kullanıcı adı ya da e-posta yanlış.'),
(75, 'Reset', 'Sıfırla'),
(76, 'Cancel', 'Vazgeç'),
(77, 'OK', 'Tamam'),
(78, '%s role defined.', '%s rol tanımı yapıldı.'),
(79, 'Nothing chosen.', 'Hiçbir şey seçilmedi.'),
(80, 'Please check wrong fields.', 'Lütfen hatalı alanları kontrol edin.'),
(81, 'Are you sure to remove that record?', 'Bu kaydı silmek istediğinize emin misiniz?'),
(82, 'Delete', 'Sil'),
(83, 'Browse', 'Gözat'),
(84, 'Clean', 'Temizle'),
(85, 'Update', 'Güncelle'),
(86, 'Password reset successfully.', 'Şifre başarıyla sıfırlandı.'),
(87, 'This link maybe used before.', 'Bu bağlantı daha önce kullanılmış olabilir.'),
(88, 'Password reset e-mail has send.', 'Şifre sıfırlama e-postası gönderildi.'),
(89, 'Sorry', 'Maalesef'),
(90, 'Page Not Found', 'Sayfa Bulunamadı.'),
(91, 'Record inserted succesfully.', 'Kayıt başarıyla eklendi.'),
(92, '%s user definition', '%s kullanıcı tanımı'),
(93, 'Are you sure want to drop table %s?', '%s tablosunu kaldırmak istediğinize emin misiniz?'),
(94, 'Out of %d records %d-%d is monitoring.', 'Toplan %d kayıttan %d-%d arası görüntüleniyor.'),
(95, 'Invalid key.', 'Geçersiz anahtar.'),
(96, 'Due to untrusted transactions, your IP has been blocked.', 'Güvenilmez istekler nedeniyle IP adresiniz engellenmiştir.'),
(97, 'Your user account has been blocked. Please login after reset password.', 'Hesabınız kilitlenmiştir. Lütfen şifrenizi sıfırlayarak sisteme yeniden giriş yapın.'),
(98, 'Are you sure want to remove user %s?', '%s kullanıcısını silmek istediğinize emin misiniz?'),
(99, 'File cannot uploaded.', 'Dosya yüklenemedi.'),
(100, 'Translations', 'Çeviri'),
(101, 'Import', 'İçe aktar'),
(102, 'Export', 'Dışa aktar'),
(103, 'This option only imports translations on translations/translations.json file.', 'Bu seçenek sadece translations/translations.json dosyasındaki çevirileri içe aktarır.'),
(104, 'This will exports translations on translations/translations.json file.', 'Bu seçenek çevirileri, translations/translations.json dosyasına dışa aktarır.'),
(105, 'An error occured.', 'Bir hata oluştu.'),
(106, 'Exported succesfully.', 'Başarıyla dışarı aktarıldı.'),
(107, 'Imported successfully.', 'Başarıyla içe aktarıldı.'),
(108, 'Truncate Table', 'Tabloyu Boşalt'),
(109, 'Are you sure want to truncate table %s?', '%s tablosunu boşaltmak istediğinize emin misiniz?'),
(110, '%s table truncated.', '%s tablosu boşaltıldı.'),
(111, 'Last Posts', 'Son Yazılar'),
(112, 'Remember me', 'Beni Hatırla'),
(113, 'Upload file', 'Dosya yükleyin'),
(115, 'Sign In', 'Giriş Yap'),
(116, 'None', 'Hiçbiri'),
(117, 'Unauthorized Access', 'Yetkisiz Erişim');

CREATE TABLE `USERS` (
  `ID` int(11) NOT NULL,
  `USERNAME` varchar(20) DEFAULT NULL,
  `NAME` varchar(50) NOT NULL,
  `SURNAME` varchar(50) NOT NULL,
  `EMAIL` varchar(50) NOT NULL,
  `PHONE` varchar(10),
  `PASSWORD` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `CREATED_AT` datetime DEFAULT NULL,
  `ACCESS` datetime DEFAULT NULL,
  `STATUS` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `USERS` (`ID`, `USERNAME`, `NAME`, `SURNAME`, `EMAIL`, `PHONE`, `PASSWORD`, `CREATED_AT`, `ACCESS`, `STATUS`) VALUES
(1, 'root', 'Root', 'Toor', 'bakiyucel38@gmail.com', '', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', '2018-04-06 13:45:00', '2019-12-15 11:12:41', 'active'),
(2, 'guest', '', '', '', '', '', '2018-04-06 13:45:00', '2018-12-31 11:38:23', 'active');

CREATE TABLE `USERS_ROLES` (
  `ID` int(11) NOT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  `ROLE_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `USERS_ROLES` (`ID`, `USER_ID`, `ROLE_ID`) VALUES
(1, 1, 1),
(3, 1, 2);

CREATE TABLE `WATCHDOG` (
  `ID` int(11) NOT NULL,
  `EVENT` varchar(255) DEFAULT NULL,
  `VALUE` varchar(255) DEFAULT NULL,
  `DATE` datetime DEFAULT NULL,
  `IP` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `BLOCKED_IPS`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `ip` (`IP`);

ALTER TABLE `EMAILS`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `key` (`KEY`);

ALTER TABLE `RESET_PASSWORD_QUEUE`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `USER` (`USER`);

ALTER TABLE `ROLES`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `role` (`ROLE`);

ALTER TABLE `TRANSLATIONS`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `EN` (`EN`),
  ADD UNIQUE KEY `TR` (`TR`);

ALTER TABLE `USERS`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`),
  ADD UNIQUE KEY `USERNAME` (`USERNAME`);

ALTER TABLE `USERS_ROLES`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `user_id` (`USER_ID`),
  ADD KEY `role_id` (`ROLE_ID`);

ALTER TABLE `WATCHDOG`
  ADD PRIMARY KEY (`ID`);

ALTER TABLE `BLOCKED_IPS`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `EMAILS`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `RESET_PASSWORD_QUEUE`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ROLES`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `TRANSLATIONS`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

ALTER TABLE `USERS`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `USERS_ROLES`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `WATCHDOG`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `USERS_ROLES`
  ADD CONSTRAINT `USERS_ROLES_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `USERS` (`ID`),
  ADD CONSTRAINT `USERS_ROLES_ibfk_2` FOREIGN KEY (`ROLE_ID`) REFERENCES `ROLES` (`ID`);
COMMIT;";
db_query($sql);