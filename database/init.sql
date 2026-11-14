-- =====================================================================
-- init.sql - Base de datos de la Tienda Online (PostgreSQL 12)
-- Se ejecuta automáticamente la primera vez que arranca el contenedor
-- postgres-db (carpeta /docker-entrypoint-initdb.d/).
-- La base de datos "tienda" la crea Docker a partir de POSTGRES_DB,
-- por eso aquí no hace falta CREATE DATABASE.
-- =====================================================================

-- Borrado previo (orden inverso a las dependencias)
DROP TABLE IF EXISTS "productos";
DROP SEQUENCE IF EXISTS productos_id_seq;
DROP TABLE IF EXISTS "user_roles";
DROP TABLE IF EXISTS "usuarios";
DROP SEQUENCE IF EXISTS usuarios_id_seq;
DROP TABLE IF EXISTS "categorias";

-- ---------------------------------------------------------------------
-- CATEGORIAS
-- ---------------------------------------------------------------------
CREATE TABLE "public"."categorias"
(
    "is_deleted" boolean   DEFAULT false,
    "created_at" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "updated_at" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "id"         uuid                                NOT NULL,
    "nombre"     character varying(255)              NOT NULL,
    CONSTRAINT "categorias_nombre_key" UNIQUE ("nombre"),
    CONSTRAINT "categorias_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

INSERT INTO "categorias" ("is_deleted", "created_at", "updated_at", "id", "nombre")
VALUES ('f', '2023-11-02 11:43:24.717712', '2023-11-02 11:43:24.717712', 'd69cf3db-b77d-4181-b3cd-5ca8107fb6a9', 'MUJER'),
       ('f', '2023-11-02 11:43:24.717712', '2023-11-02 11:43:24.717712', '6dbcbf5e-8e1c-47cc-8578-7b0a33ebc154', 'HOMBRE'),
       ('f', '2023-11-02 11:43:24.717712', '2023-11-02 11:43:24.717712', '9def16db-362b-44c4-9fc9-77117758b5b0', 'UNISEX'),
       ('f', '2023-11-02 11:43:24.717712', '2023-11-02 11:43:24.717712', '8c5c06ba-49d6-46b6-85cc-8246c0f362bc', 'NICHO'),
       ('f', '2023-11-02 11:43:24.717712', '2023-11-02 11:43:24.717712', 'bb51d00d-13fb-4b09-acc9-948185636f79', 'ESTUCHES');

-- ---------------------------------------------------------------------
-- PRODUCTOS
-- ---------------------------------------------------------------------
-- Cuidado con las secuencias: si se borran se pierde el autoincremento
CREATE SEQUENCE productos_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."productos"
(
    "is_deleted"   boolean          DEFAULT false,
    "precio"       double precision DEFAULT '0.0',
    "stock"        integer          DEFAULT '0',
    "created_at"   timestamp        DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "id"           bigint           DEFAULT nextval('productos_id_seq') NOT NULL,
    "updated_at"   timestamp        DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "categoria_id" uuid,
    "uuid"         uuid,
    "descripcion"  character varying(255),
    "imagen"       text             DEFAULT 'https://placehold.co/150',
    "marca"        character varying(255),
    "modelo"       character varying(255),
    CONSTRAINT "productos_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "productos_uuid_key" UNIQUE ("uuid")
) WITH (oids = false);

INSERT INTO "productos" ("is_deleted", "precio", "stock", "created_at", "id", "updated_at",
                         "categoria_id", "uuid", "descripcion", "imagen", "marca", "modelo")
VALUES ('f', 145.00, 12, '2023-11-02 11:43:24.722473', 1, '2023-11-02 11:43:24.722473',
        'd69cf3db-b77d-4181-b3cd-5ca8107fb6a9', 'a0b1a8aa-9620-4514-a707-cfd275180c0a',
        'Oriental fresco con notas de naranja, rosa, pachulí y vainilla.', 'https://placehold.co/150', 'Chanel', 'Coco Mademoiselle EDP 100 ml'),
       ('f', 119.95, 8, '2023-11-02 11:43:24.722473', 2, '2023-11-02 11:43:24.722473',
        'd69cf3db-b77d-4181-b3cd-5ca8107fb6a9', 'bbdc21cd-61a1-41fc-bce0-545b01730847',
        'Gourmand floral con iris, praliné y vainilla.', 'https://placehold.co/150', 'Lancôme', 'La Vie Est Belle EDP 100 ml'),
       ('f', 112.00, 6, '2023-11-02 11:43:24.722473', 3, '2023-11-02 11:43:24.722473',
        'd69cf3db-b77d-4181-b3cd-5ca8107fb6a9', 'd8acc08a-fc50-41fe-b703-160e35ca65cf',
        'Floral oriental con jazmín, nardo, haba tonka y cacao.', 'https://placehold.co/150', 'Carolina Herrera', 'Good Girl EDP 80 ml'),
       ('f', 118.00, 15, '2023-11-02 11:43:24.722473', 4, '2023-11-02 11:43:24.722473',
        '6dbcbf5e-8e1c-47cc-8578-7b0a33ebc154', '32617fd0-93e3-4c8e-a689-e6b547611964',
        'Aromático fresco con bergamota, pimienta y ambroxan.', 'https://placehold.co/150', 'Dior', 'Sauvage EDT 100 ml'),
       ('f', 92.50, 10, '2023-11-02 11:43:24.722473', 5, '2023-11-02 11:43:24.722473',
        '6dbcbf5e-8e1c-47cc-8578-7b0a33ebc154', '69978dd7-f828-44b6-a9a4-ba747ce40348',
        'Especiado con mandarina, canela, cuero y ámbar.', 'https://placehold.co/150', 'Paco Rabanne', '1 Million EDT 100 ml'),
       ('f', 89.90, 4, '2023-11-02 11:43:24.722473', 6, '2023-11-02 11:43:24.722473',
        '6dbcbf5e-8e1c-47cc-8578-7b0a33ebc154', 'e371d9d2-5f1f-4799-8b18-162c3bb3a7cb',
        'Amaderado con manzana, canela y sándalo.', 'https://placehold.co/150', 'Hugo Boss', 'Boss Bottled EDP 100 ml'),
       ('f', 49.95, 20, '2023-11-02 11:43:24.722473', 7, '2023-11-02 11:43:24.722473',
        '9def16db-362b-44c4-9fc9-77117758b5b0', '9f6fbff9-33de-4223-81e8-2e0b9b642182',
        'Cítrico fresco con bergamota, té verde y almizcle.', 'https://placehold.co/150', 'Calvin Klein', 'CK One EDT 200 ml'),
       ('f', 142.00, 5, '2023-11-02 11:43:24.722473', 8, '2023-11-02 11:43:24.722473',
        '9def16db-362b-44c4-9fc9-77117758b5b0', '7653950d-a46e-4ae3-921e-6ebbfde506c1',
        'Amaderado mineral con salvia y sal marina.', 'https://placehold.co/150', 'Jo Malone London', 'Wood Sage & Sea Salt Cologne 100 ml'),
       ('f', 245.00, 3, '2023-11-02 11:43:24.722473', 9, '2023-11-02 11:43:24.722473',
        '8c5c06ba-49d6-46b6-85cc-8246c0f362bc', '8330265a-9ff3-48de-a8d7-aca4908c8036',
        'Ambarado floral con azafrán, jazmín y madera de cedro.', 'https://placehold.co/150', 'Maison Francis Kurkdjian', 'Baccarat Rouge 540 EDP 70 ml'),
       ('f', 210.00, 2, '2023-11-02 11:43:24.722473', 10, '2023-11-02 11:43:24.722473',
        '8c5c06ba-49d6-46b6-85cc-8246c0f362bc', '9c84ec29-a710-40f3-97b8-8471b7983263',
        'Amaderado con sándalo, cardamomo y cuero.', 'https://placehold.co/150', 'Le Labo', 'Santal 33 EDP 50 ml'),
       ('f', 95.00, 7, '2023-11-02 11:43:24.722473', 11, '2023-11-02 11:43:24.722473',
        'bb51d00d-13fb-4b09-acc9-948185636f79', '6502df2c-389e-43b4-a1d9-e10683a0f80f',
        'Estuche con eau de toilette de 125 ml y gel de ducha de 75 ml.', 'https://placehold.co/150', 'Jean Paul Gaultier', 'Le Male Estuche EDT 125 ml');

-- Como los ids se han insertado a mano, se sincroniza la secuencia
SELECT setval('productos_id_seq', (SELECT MAX(id) FROM productos));

-- ---------------------------------------------------------------------
-- USUARIOS
-- ---------------------------------------------------------------------
CREATE SEQUENCE usuarios_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."usuarios"
(
    "is_deleted" boolean   DEFAULT false,
    "created_at" timestamp DEFAULT CURRENT_TIMESTAMP           NOT NULL,
    "id"         bigint    DEFAULT nextval('usuarios_id_seq') NOT NULL,
    "updated_at" timestamp DEFAULT CURRENT_TIMESTAMP           NOT NULL,
    "apellidos"  character varying(255)                        NOT NULL,
    "email"      character varying(255)                        NOT NULL,
    "nombre"     character varying(255)                        NOT NULL,
    "password"   character varying(255)                        NOT NULL,
    "username"   character varying(255)                        NOT NULL,
    CONSTRAINT "usuarios_email_key" UNIQUE ("email"),
    CONSTRAINT "usuarios_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "usuarios_username_key" UNIQUE ("username")
) WITH (oids = false);

-- Usuarios de prueba (contraseñas cifradas con bcrypt mediante password_hash):
--   admin / Admin1    -> roles USER + ADMIN
--   user  / User1234  -> rol USER
--   test  / test1234  -> rol USER
INSERT INTO "usuarios" ("is_deleted", "created_at", "id", "updated_at", "apellidos", "email", "nombre", "password", "username")
VALUES ('f', '2023-11-02 11:43:24.724871', 1, '2023-11-02 11:43:24.724871', 'Admin Admin',  'admin@prueba.net', 'Admin', '$2y$10$ZMWS8YSxrLfjmYPNLP2Qxe8xUMpD6dhUsbxA2IMPQYo6KK0Bc38fG', 'admin'),
       ('f', '2023-11-02 11:43:24.730431', 2, '2023-11-02 11:43:24.730431', 'User User',    'user@prueba.net',  'User',  '$2y$10$1jlwsCDaLmf8QoICV0BsEO9kzs4SvHUng5VIjoTVw93Dyobtiei9S', 'user'),
       ('f', '2023-11-02 11:43:24.733552', 3, '2023-11-02 11:43:24.733552', 'Test Test',    'test@prueba.net',  'Test',  '$2y$10$rZjTNBRqrKKCkR5Selh9HuXw5qs4P8hVgZ3k54ea2tTiDH0LY89lm', 'test');

SELECT setval('usuarios_id_seq', (SELECT MAX(id) FROM usuarios));

-- ---------------------------------------------------------------------
-- USER_ROLES
-- ---------------------------------------------------------------------
CREATE TABLE "public"."user_roles"
(
    "user_id" bigint NOT NULL,
    "roles"   character varying(255)
) WITH (oids = false);

INSERT INTO "user_roles" ("user_id", "roles")
VALUES (1, 'USER'),
       (1, 'ADMIN'),
       (2, 'USER'),
       (3, 'USER');

-- ---------------------------------------------------------------------
-- CLAVES AJENAS
-- ---------------------------------------------------------------------
ALTER TABLE ONLY "public"."productos"
    ADD CONSTRAINT "fk_productos_categoria" FOREIGN KEY (categoria_id) REFERENCES categorias (id) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."user_roles"
    ADD CONSTRAINT "fk_user_roles_usuario" FOREIGN KEY (user_id) REFERENCES usuarios (id) NOT DEFERRABLE;
