
CREATE TABLE IF NOT EXISTS `product_test` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 150 ) NOT NULL ,
`price` FLOAT NOT NULL,
`create_date` datetime default NULL,
`promo` BOOL NOT NULL default 0,
`dummy` set('created','started','stopped') DEFAULT NULL,
`metadata` JSON default NULL
) ENGINE = InnoDB ;

CREATE TABLE IF NOT EXISTS `product_tags_test` (
`product_id` INT NOT NULL ,
`tag` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( `product_id` , `tag` )
) ENGINE = InnoDb ;

CREATE TABLE IF NOT EXISTS `labels_test` (
`key` INT NOT NULL ,
`keyalias` VARCHAR( 10 ) NULL,
`lang` VARCHAR( 5 ) NOT NULL ,
`label` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( `key` , `lang` ),
UNIQUE (`keyalias`)
) ENGINE=InnoDb ;

CREATE TABLE IF NOT EXISTS `labels1_test` (
`key` INT NOT NULL ,
`keyalias` VARCHAR( 10 ) NOT NULL,
`lang` VARCHAR( 5 ) NOT NULL,
`label` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( `key`),
UNIQUE (`keyalias`)
) ENGINE=InnoDb ;

-- for the crud example
CREATE TABLE IF NOT EXISTS `products` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 150 ) NOT NULL ,
`price` FLOAT   default '0',
`promo` BOOL NOT NULL,
`publish_date` DATE NOT NULL
) ENGINE = InnoDb ;


CREATE TABLE IF NOT EXISTS `testkvdb` (
`k_key` VARCHAR( 50 ) NOT NULL ,
`k_value` longblob NOT NULL ,
`k_expire` DATETIME NOT NULL ,
PRIMARY KEY ( `k_key` )
) ENGINE = InnoDb;
