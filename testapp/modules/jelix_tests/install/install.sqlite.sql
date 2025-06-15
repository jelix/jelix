CREATE TABLE product_test (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR( 150 ) NOT NULL ,
    price FLOAT NOT NULL,
    create_date datetime default NULL,
    promo BOOL NOT NULL default 0,
    dummy varchar(10) DEFAULT NULL,
    metadata TEXT DEFAULT NULL
);

CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name varchar(150) not null,
    price float default 0
);

CREATE TABLE product_tags_test (
    product_id INTEGER,
    tag VARCHAR( 50 ) NOT NULL,
    PRIMARY KEY ( product_id, tag)
);