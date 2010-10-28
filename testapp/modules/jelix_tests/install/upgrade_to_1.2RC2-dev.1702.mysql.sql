CREATE TABLE `labels1_test` (
`key` INT NOT NULL ,
`lang` VARCHAR( 5 ) NOT NULL,
`label` VARCHAR( 50 ) NOT NULL ,
`keyalias` VARCHAR( 10 ) NOT NULL,
PRIMARY KEY ( `key`),
UNIQUE(`keyalias`)
);

ALTER TABLE `labels_test` ADD `keyalias` VARCHAR( 10 ) NULL ,
ADD UNIQUE (
`keyalias`
)