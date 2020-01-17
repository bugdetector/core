<?php
$logins_table_create_query = "CREATE TABLE `LOGINS` (
    `ID` int(11) PRIMARY KEY AUTO_INCREMENT,
    `IP_ADDRESS` varchar(16) DEFAULT NULL,
    `USERNAME` varchar(255) DEFAULT NULL,
    `DATE` DATETIME 
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
  db_query($logins_table_create_query);
