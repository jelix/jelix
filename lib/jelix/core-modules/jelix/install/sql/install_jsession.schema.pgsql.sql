CREATE TABLE jsession (
    id character varying(64) NOT NULL,
    creation time with time zone NOT NULL,
    "access" time with time zone NOT NULL,
    data text NOT NULL
);

ALTER TABLE ONLY jsession
    ADD CONSTRAINT jsession_pkey PRIMARY KEY (id);