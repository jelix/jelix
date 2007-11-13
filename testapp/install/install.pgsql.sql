--
-- Name: products; Type: TABLE; Schema: public; Tablespace: 
--

CREATE TABLE products (
    id integer NOT NULL,
    name character varying(150) NOT NULL,
    price real DEFAULT 0
);


CREATE SEQUENCE products_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER SEQUENCE products_id_seq OWNED BY products.id;
ALTER TABLE products ALTER COLUMN id SET DEFAULT nextval('products_id_seq'::regclass);
ALTER TABLE ONLY products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);

ALTER TABLE public.products OWNER TO postgres;
ALTER TABLE public.products_id_seq OWNER TO postgres;

--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--
REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;