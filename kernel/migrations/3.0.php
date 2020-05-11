<?php
db_query("

--
-- Veritabanı: `core`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `blocked_ips`
--

CREATE TABLE `blocked_ips` (
  `ID` int NOT NULL,
  `ip` varchar(16) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains blocked IP adresses. These IP adresses can''t login to site.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `cache`
--

CREATE TABLE `cache` (
  `ID` int NOT NULL,
  `bundle` varchar(255) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `value` longtext,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains cached data. You can use this table via Cache class.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `emails`
--

CREATE TABLE `emails` (
  `ID` int NOT NULL,
  `key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `en` longtext CHARACTER SET utf8 COLLATE utf8_general_ci,
  `tr` longtext CHARACTER SET utf8 COLLATE utf8_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains e-mail template in many languages. You can add new one.';

--
-- Tablo döküm verisi `emails`
--

INSERT INTO `emails` (`ID`, `key`, `en`, `tr`, `created_at`) VALUES
(1, 'password_reset', '&#60;p&#62;You can reset your password using the link below.&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;/p&#62;', '&#60;p&#62;Aşağıdaki bağlantıyı kullanarak şifrenizi sıfırlayabilirsiniz.&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;/p&#62;', '2020-05-06 22:45:37'),
(2, 'user_insert', '&#60;p&#62;Your e-mail has defined as an account on %s. Before login you must set a password for your account. You can set a password using link below.&#60;/p&#62;&#60;p&#62;Your username : %s&#60;br&#62;&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;br&#62;&#60;/p&#62;', '&#60;p&#62;E-postanız, %s üzerinde bir hesap için tanımlandı. Oturum açmadan önce bir şifre belirlemeniz gerekiyor. Şifre belirleme işlemi için aşağıdaki bağlantıyı kullanabilirsiniz.&#60;/p&#62;&#60;p&#62;Kullanıcı adınız : %s&#60;br&#62;&#60;/p&#62;&#60;p&#62;&#60;a href=&#34;http://%s&#34; target=&#34;_blank&#34;&#62;%s&#60;/a&#62;&#60;br&#62;&#60;/p&#62;', '2020-05-06 22:45:37');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `logins`
--

CREATE TABLE `logins` (
  `ID` int NOT NULL,
  `ip_address` varchar(16) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains failed login attempts by user and IP address to site.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `reset_password_queue`
--

CREATE TABLE `reset_password_queue` (
  `ID` int NOT NULL,
  `user` int DEFAULT NULL,
  `key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains reset password request tokens.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `roles`
--

CREATE TABLE `roles` (
  `ID` int NOT NULL,
  `role` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains roles. You can add new one.';

--
-- Tablo döküm verisi `roles`
--

INSERT INTO `roles` (`ID`, `role`, `created_at`) VALUES
(1, 'ADMIN', '2020-05-06 23:08:58'),
(2, 'USER', '2020-05-06 23:08:58');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `translations`
--

CREATE TABLE `translations` (
  `ID` int NOT NULL,
  `key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `en` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `tr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains site translations. You can add new one.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `ID` int NOT NULL,
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `surname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `email` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `access` datetime DEFAULT NULL,
  `status` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains site Users fundemantal data. Connected with User class.';

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`ID`, `username`, `name`, `surname`, `email`, `phone`, `password`, `created_at`, `access`, `status`) VALUES
(1, 'root', 'Root', 'Toor', 'bakiyucel38@gmail.com', '+905079158686', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', '2018-04-06 13:45:00', '2020-05-11 01:16:56', NULL),
(2, 'guest', '', '', '', '', '', '2018-04-06 13:45:00', '2018-12-31 11:38:23', 'active');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users_roles`
--

CREATE TABLE `users_roles` (
  `ID` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `role_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Constains User''s roles. Look at User::add_role, User::delete_role, User::updateRoles';

--
-- Tablo döküm verisi `users_roles`
--

INSERT INTO `users_roles` (`ID`, `user_id`, `role_id`, `created_at`) VALUES
(1, 1, 1, '2020-05-06 23:09:18');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `variables`
--

CREATE TABLE `variables` (
  `ID` int NOT NULL,
  `key` varchar(255) DEFAULT NULL,
  `value` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains less secure site Variables. For more security use .config.php. Connected vie Variable class.';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `watchdog`
--

CREATE TABLE `watchdog` (
  `ID` int NOT NULL,
  `event` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `ip` varchar(16) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Importtant log of actions.';

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `blocked_ips`
--
ALTER TABLE `blocked_ips`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `ip` (`ip`);

--
-- Tablo için indeksler `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`ID`);

--
-- Tablo için indeksler `emails`
--
ALTER TABLE `emails`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Tablo için indeksler `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`ID`);

--
-- Tablo için indeksler `reset_password_queue`
--
ALTER TABLE `reset_password_queue`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `USER` (`user`);

--
-- Tablo için indeksler `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `role` (`role`);

--
-- Tablo için indeksler `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `EN` (`en`),
  ADD UNIQUE KEY `TR` (`tr`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `USERNAME` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `users_roles`
--
ALTER TABLE `users_roles`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Tablo için indeksler `variables`
--
ALTER TABLE `variables`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Tablo için indeksler `watchdog`
--
ALTER TABLE `watchdog`
  ADD PRIMARY KEY (`ID`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `blocked_ips`
--
ALTER TABLE `blocked_ips`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `cache`
--
ALTER TABLE `cache`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `emails`
--
ALTER TABLE `emails`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `logins`
--
ALTER TABLE `logins`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `reset_password_queue`
--
ALTER TABLE `reset_password_queue`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `roles`
--
ALTER TABLE `roles`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `translations`
--
ALTER TABLE `translations`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `users_roles`
--
ALTER TABLE `users_roles`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `variables`
--
ALTER TABLE `variables`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `watchdog`
--
ALTER TABLE `watchdog`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `users_roles`
--
ALTER TABLE `users_roles`
  ADD CONSTRAINT `users_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`),
  ADD CONSTRAINT `users_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`ID`);
COMMIT;
");