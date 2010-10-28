
CREATE TABLE `product_test` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 150 ) NOT NULL ,
`price` FLOAT NOT NULL,
`create_date` datetime default NULL,
`promo` BOOL NOT NULL 
) TYPE = MYISAM ;

CREATE TABLE `product_tags_test` (
`product_id` INT NOT NULL ,
`tag` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( `product_id` , `tag` )
) ENGINE = MYISAM ;

CREATE TABLE `labels_test` (
`key` INT NOT NULL ,
`keyalias` VARCHAR( 10 ) NULL,
`lang` VARCHAR( 5 ) NOT NULL ,
`label` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( `key` , `lang` ),
UNIQUE (`keyalias`)
);

CREATE TABLE `labels1_test` (
`key` INT NOT NULL ,
`keyalias` VARCHAR( 10 ) NOT NULL,
`lang` VARCHAR( 5 ) NOT NULL,
`label` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( `key`),
UNIQUE (`keyalias`)
);

-- for the crud example
CREATE TABLE `products` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 150 ) NOT NULL ,
`price` FLOAT   default '0',
`promo` BOOL NOT NULL 
) TYPE = MYISAM ;


CREATE TABLE `testkvdb` (
`k_key` VARCHAR( 50 ) NOT NULL ,
`k_value` longblob NOT NULL ,
`k_expire` DATETIME NOT NULL ,
PRIMARY KEY ( `k_key` )
) ENGINE = MYISAM;
