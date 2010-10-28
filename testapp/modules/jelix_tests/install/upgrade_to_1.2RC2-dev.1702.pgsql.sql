CREATE TABLE labels1_tests (
    "key" integer NOT NULL,
    lang character varying(5) NOT NULL,
    label character varying(50) NOT NULL
);

ALTER TABLE labels_test ADD keyalias VARCHAR( 10 ) NULL;

ALTER TABLE ONLY labels1_tests
    ADD CONSTRAINT labels1_tests_pkey PRIMARY KEY ("key");

ALTER TABLE ONLY labels1_tests
    ADD CONSTRAINT labels1_tests_keyalias UNIQUE KEY ("keyalias");

ALTER TABLE ONLY labels_tests
    ADD CONSTRAINT labels_tests_keyalias UNIQUE KEY ("keyalias");
