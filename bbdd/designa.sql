--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.15
-- Dumped by pg_dump version 9.2.2
-- Started on 2015-09-08 07:33:36

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

DROP DATABASE designa;
--
-- TOC entry 2364 (class 1262 OID 34089)
-- Name: designa; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE designa WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'Spanish_Argentina.1252' LC_CTYPE = 'Spanish_Argentina.1252';


ALTER DATABASE designa OWNER TO postgres;

\connect designa

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- TOC entry 5 (class 2615 OID 2200)
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA public;


ALTER SCHEMA public OWNER TO postgres;

--
-- TOC entry 2365 (class 0 OID 0)
-- Dependencies: 5
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'standard public schema';


--
-- TOC entry 239 (class 3079 OID 11639)
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- TOC entry 2367 (class 0 OID 0)
-- Dependencies: 239
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 176 (class 1259 OID 34196)
-- Name: area; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE area (
    idarea integer NOT NULL,
    iddepto integer NOT NULL,
    descripcion character(80) DEFAULT ''::bpchar NOT NULL
);


ALTER TABLE public.area OWNER TO postgres;

--
-- TOC entry 202 (class 1259 OID 34593)
-- Name: asignacion_materia; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE asignacion_materia (
    id_designacion integer NOT NULL,
    id_materia integer NOT NULL,
    nro_tab8 integer NOT NULL,
    rol character(4),
    id_periodo integer,
    externa character(1),
    modulo integer NOT NULL,
    carga_horaria integer,
    anio integer
);


ALTER TABLE public.asignacion_materia OWNER TO postgres;

--
-- TOC entry 169 (class 1259 OID 34155)
-- Name: caracter; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE caracter (
    id_car character(1) NOT NULL,
    descripcion character(20) DEFAULT ''::bpchar NOT NULL
);


ALTER TABLE public.caracter OWNER TO postgres;

--
-- TOC entry 165 (class 1259 OID 34113)
-- Name: categ_estatuto; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE categ_estatuto (
    codigo_est character(10) DEFAULT ''::bpchar NOT NULL,
    descripcion character(50) DEFAULT ''::bpchar NOT NULL
);


ALTER TABLE public.categ_estatuto OWNER TO postgres;

--
-- TOC entry 166 (class 1259 OID 34120)
-- Name: categ_siu; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE categ_siu (
    codigo_siu character(4) DEFAULT ''::bpchar NOT NULL,
    descripcion character(45) DEFAULT ''::bpchar NOT NULL,
    escalafon character(1)
);


ALTER TABLE public.categ_siu OWNER TO postgres;

--
-- TOC entry 203 (class 1259 OID 34628)
-- Name: categoria_invest; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE categoria_invest (
    cod_cati integer NOT NULL,
    descripcion character(10)
);


ALTER TABLE public.categoria_invest OWNER TO postgres;

--
-- TOC entry 174 (class 1259 OID 34179)
-- Name: cic_conicef; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cic_conicef (
    id character(3) NOT NULL,
    descripcion character(50) DEFAULT ''::bpchar NOT NULL
);


ALTER TABLE public.cic_conicef OWNER TO postgres;

--
-- TOC entry 167 (class 1259 OID 34127)
-- Name: dedicacion; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE dedicacion (
    id_ded integer NOT NULL,
    descripcion character(20) DEFAULT ''::bpchar NOT NULL
);


ALTER TABLE public.dedicacion OWNER TO postgres;

--
-- TOC entry 173 (class 1259 OID 34172)
-- Name: dedicacion_incentivo; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE dedicacion_incentivo (
    id_di integer NOT NULL,
    descripcion character(30) DEFAULT ''::bpchar NOT NULL
);


ALTER TABLE public.dedicacion_incentivo OWNER TO postgres;

--
-- TOC entry 172 (class 1259 OID 34170)
-- Name: dedicacion_incentivo_id_di_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE dedicacion_incentivo_id_di_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dedicacion_incentivo_id_di_seq OWNER TO postgres;

--
-- TOC entry 2368 (class 0 OID 0)
-- Dependencies: 172
-- Name: dedicacion_incentivo_id_di_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE dedicacion_incentivo_id_di_seq OWNED BY dedicacion_incentivo.id_di;


--
-- TOC entry 175 (class 1259 OID 34185)
-- Name: departamento; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE departamento (
    iddepto integer NOT NULL,
    idunidad_academica character(5) NOT NULL,
    descripcion character(60) DEFAULT ''::bpchar NOT NULL
);


ALTER TABLE public.departamento OWNER TO postgres;

--
-- TOC entry 191 (class 1259 OID 34353)
-- Name: designacion; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE designacion (
    id_designacion integer NOT NULL,
    id_docente integer,
    nro_cargo integer,
    anio_acad integer,
    desde date NOT NULL,
    hasta date,
    cat_mapuche character(4),
    cat_estat character(6),
    dedic integer NOT NULL,
    carac character(1) NOT NULL,
    uni_acad character(5) NOT NULL,
    id_departamento integer NOT NULL,
    id_area integer NOT NULL,
    id_orientacion integer NOT NULL,
    id_norma integer,
    id_expediente integer,
    tipo_incentivo integer,
    dedi_incen integer,
    cic_con character(3),
    cargo_gestion character(4),
    ord_gestion character(10),
    emite_cargo_gestion character(4),
    nro_gestion character(10),
    observaciones character(100),
    check_presup integer,
    nro_540 integer,
    concursado integer,
    check_academica integer,
    tipo_desig integer,
    id_reserva integer
);


ALTER TABLE public.designacion OWNER TO postgres;

--
-- TOC entry 190 (class 1259 OID 34351)
-- Name: designacion_id_designacion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE designacion_id_designacion_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.designacion_id_designacion_seq OWNER TO postgres;

--
-- TOC entry 2369 (class 0 OID 0)
-- Dependencies: 190
-- Name: designacion_id_designacion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE designacion_id_designacion_seq OWNED BY designacion.id_designacion;


--
-- TOC entry 234 (class 1259 OID 45064)
-- Name: designacionh; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE designacionh (
    id_designacion integer NOT NULL,
    id_docente integer NOT NULL,
    nro_cargo integer,
    anio_acad integer NOT NULL,
    desde date NOT NULL,
    hasta date NOT NULL,
    cat_mapuche character(4),
    cat_estat character(5),
    dedic integer NOT NULL,
    carac character(1) NOT NULL,
    uni_acad character(5) NOT NULL,
    id_departamento integer NOT NULL,
    id_area integer NOT NULL,
    id_orientacion integer NOT NULL,
    id_norma integer,
    id_expediente integer,
    tipo_incentivo integer,
    dedi_incen integer,
    cic_con character(3),
    cargo_gestion character(4),
    ord_gestion character(10),
    emite_cargo_gestion character(4),
    nro_gestion character(10),
    observaciones character(100),
    check_presup integer,
    nro_540 integer,
    concursado integer,
    check_academica integer,
    tipo_desig integer,
    id_reserva integer
);


ALTER TABLE public.designacionh OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 45062)
-- Name: designacionh_id_designacion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE designacionh_id_designacion_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.designacionh_id_designacion_seq OWNER TO postgres;

--
-- TOC entry 2370 (class 0 OID 0)
-- Dependencies: 233
-- Name: designacionh_id_designacion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE designacionh_id_designacion_seq OWNED BY designacionh.id_designacion;


--
-- TOC entry 180 (class 1259 OID 34233)
-- Name: docente; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE docente (
    id_docente integer NOT NULL,
    legajo integer,
    apellido character(30),
    nombre character(30),
    nro_tabla integer,
    tipo_docum character(4),
    nro_docum integer,
    fec_nacim date,
    nro_cuil1 integer,
    nro_cuil integer,
    nro_cuil2 integer,
    tipo_sexo character(1),
    anioingreso integer,
    pais_nacim character(2),
    porcdedicdocente double precision,
    porcdedicinvestig double precision,
    porcdedicagestion double precision,
    porcdedicaextens double precision,
    pcia_nacim integer
);


ALTER TABLE public.docente OWNER TO postgres;

--
-- TOC entry 200 (class 1259 OID 34566)
-- Name: en_conjunto; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE en_conjunto (
    id_conjunto integer NOT NULL,
    ua character(5) NOT NULL,
    id_materia integer NOT NULL
);


ALTER TABLE public.en_conjunto OWNER TO postgres;

--
-- TOC entry 199 (class 1259 OID 34564)
-- Name: en_conjunto_id_conjunto_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE en_conjunto_id_conjunto_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.en_conjunto_id_conjunto_seq OWNER TO postgres;

--
-- TOC entry 2371 (class 0 OID 0)
-- Dependencies: 199
-- Name: en_conjunto_id_conjunto_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE en_conjunto_id_conjunto_seq OWNED BY en_conjunto.id_conjunto;


--
-- TOC entry 181 (class 1259 OID 34253)
-- Name: entidad_otorgante; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE entidad_otorgante (
    cod_entidad character(6) NOT NULL,
    nombre character(200),
    cod_ciudad integer
);


ALTER TABLE public.entidad_otorgante OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 44832)
-- Name: escalafon; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE escalafon (
    id_escalafon character(1) NOT NULL,
    descripcion character(15) NOT NULL
);


ALTER TABLE public.escalafon OWNER TO postgres;

--
-- TOC entry 189 (class 1259 OID 34327)
-- Name: expediente; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE expediente (
    id_exp integer NOT NULL,
    nro_exp character(1),
    tipo_exp character(5),
    emite_tipo character(4),
    fecha date,
    pdf bytea
);


ALTER TABLE public.expediente OWNER TO postgres;

--
-- TOC entry 188 (class 1259 OID 34325)
-- Name: expediente_id_exp_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE expediente_id_exp_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.expediente_id_exp_seq OWNER TO postgres;

--
-- TOC entry 2372 (class 0 OID 0)
-- Dependencies: 188
-- Name: expediente_id_exp_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE expediente_id_exp_seq OWNED BY expediente.id_exp;


--
-- TOC entry 209 (class 1259 OID 34741)
-- Name: funcion_extension; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE funcion_extension (
    id_extension character(5) NOT NULL,
    descripcion character varying(70)
);


ALTER TABLE public.funcion_extension OWNER TO postgres;

--
-- TOC entry 204 (class 1259 OID 34633)
-- Name: funcion_investigador; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE funcion_investigador (
    id_funcion character(4) NOT NULL,
    descripcion character(70)
);


ALTER TABLE public.funcion_investigador OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 44746)
-- Name: impresion_540; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE impresion_540 (
    id integer NOT NULL,
    fecha_impresion date
);


ALTER TABLE public.impresion_540 OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 44744)
-- Name: impresion_540_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE impresion_540_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.impresion_540_id_seq OWNER TO postgres;

--
-- TOC entry 2373 (class 0 OID 0)
-- Dependencies: 217
-- Name: impresion_540_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE impresion_540_id_seq OWNED BY impresion_540.id;


--
-- TOC entry 192 (class 1259 OID 34443)
-- Name: imputacion; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE imputacion (
    id_designacion integer NOT NULL,
    porc integer,
    id_programa integer NOT NULL
);


ALTER TABLE public.imputacion OWNER TO postgres;

--
-- TOC entry 171 (class 1259 OID 34163)
-- Name: incentivo; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE incentivo (
    id_inc integer NOT NULL,
    descripcion character(20) DEFAULT ''::bpchar NOT NULL
);


ALTER TABLE public.incentivo OWNER TO postgres;

--
-- TOC entry 170 (class 1259 OID 34161)
-- Name: incentivo_id_inc_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE incentivo_id_inc_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.incentivo_id_inc_seq OWNER TO postgres;

--
-- TOC entry 2374 (class 0 OID 0)
-- Dependencies: 170
-- Name: incentivo_id_inc_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE incentivo_id_inc_seq OWNED BY incentivo.id_inc;


--
-- TOC entry 201 (class 1259 OID 34582)
-- Name: inscriptos; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE inscriptos (
    inscriptos integer,
    anio_acad integer NOT NULL,
    id_materia integer NOT NULL
);


ALTER TABLE public.inscriptos OWNER TO postgres;

--
-- TOC entry 213 (class 1259 OID 34784)
-- Name: integrante_externo_pe; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE integrante_externo_pe (
    apellido character(20),
    nombre character(20),
    nro_tabla integer,
    tipo_docum character(4) NOT NULL,
    nro_docum integer NOT NULL,
    tipo_sexo character(1),
    pais_nacim character(2),
    id_pext integer NOT NULL,
    funcion_p character(4),
    carga_horaria integer,
    institucion character(20),
    pcia_nacim integer
);


ALTER TABLE public.integrante_externo_pe OWNER TO postgres;

--
-- TOC entry 208 (class 1259 OID 34706)
-- Name: integrante_externo_pi; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE integrante_externo_pi (
    apellido character(20),
    nombre character(20),
    nro_tabla integer,
    tipo_docum character(4) NOT NULL,
    nro_docum integer NOT NULL,
    fec_nacim date,
    nro_cuil1 integer,
    nro_cuil integer,
    nro_cuil2 integer,
    tipo_sexo character(1),
    pais_nacim character(2),
    pinvest integer NOT NULL,
    cat_invest integer,
    identificador_personal character(10),
    funcion_p character(4),
    carga_horaria integer,
    institucion character(30),
    pcia_nacim integer
);


ALTER TABLE public.integrante_externo_pi OWNER TO postgres;

--
-- TOC entry 212 (class 1259 OID 34764)
-- Name: integrante_interno_pe; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE integrante_interno_pe (
    id_docente integer NOT NULL,
    id_pext integer NOT NULL,
    funcion_p character(4),
    carga_horaria integer,
    ua character(5)
);


ALTER TABLE public.integrante_interno_pe OWNER TO postgres;

--
-- TOC entry 207 (class 1259 OID 34656)
-- Name: integrante_interno_pi; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE integrante_interno_pi (
    id_docente integer NOT NULL,
    pinvest integer NOT NULL,
    funcion_p character(4),
    cat_investigador integer,
    identificador_personal character(10),
    carga_horaria integer,
    ua character(4)
);


ALTER TABLE public.integrante_interno_pi OWNER TO postgres;

--
-- TOC entry 216 (class 1259 OID 36671)
-- Name: localidad; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE localidad (
    id integer NOT NULL,
    id_provincia integer NOT NULL,
    localidad character varying(255) NOT NULL
);


ALTER TABLE public.localidad OWNER TO postgres;

--
-- TOC entry 168 (class 1259 OID 34133)
-- Name: macheo_categ; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE macheo_categ (
    catsiu character(4) DEFAULT ''::bpchar NOT NULL,
    catest character(10) DEFAULT ''::bpchar NOT NULL,
    id_ded integer NOT NULL
);


ALTER TABLE public.macheo_categ OWNER TO postgres;

--
-- TOC entry 198 (class 1259 OID 34528)
-- Name: materia; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE materia (
    id_materia integer NOT NULL,
    id_plan integer NOT NULL,
    cod_siu character(1) NOT NULL,
    desc_materia character(100),
    orden_materia integer NOT NULL,
    anio_segunplan integer,
    horas_semanales integer,
    periodo_dictado integer,
    periodo_dictado_real integer,
    id_departamento integer,
    id_area integer,
    id_orientacion integer
);


ALTER TABLE public.materia OWNER TO postgres;

--
-- TOC entry 197 (class 1259 OID 34526)
-- Name: materia_id_materia_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE materia_id_materia_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.materia_id_materia_seq OWNER TO postgres;

--
-- TOC entry 2375 (class 0 OID 0)
-- Dependencies: 197
-- Name: materia_id_materia_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE materia_id_materia_seq OWNED BY materia.id_materia;


--
-- TOC entry 223 (class 1259 OID 44882)
-- Name: mocovi_costo_categoria; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE mocovi_costo_categoria (
    id_costo_categoria integer NOT NULL,
    id_periodo integer,
    codigo_siu character(4),
    costo_basico numeric(10,3),
    costo_diario numeric(10,3)
);


ALTER TABLE public.mocovi_costo_categoria OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 44880)
-- Name: mocovi_costo_categoria_id_costo_categoria_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE mocovi_costo_categoria_id_costo_categoria_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mocovi_costo_categoria_id_costo_categoria_seq OWNER TO postgres;

--
-- TOC entry 2376 (class 0 OID 0)
-- Dependencies: 222
-- Name: mocovi_costo_categoria_id_costo_categoria_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE mocovi_costo_categoria_id_costo_categoria_seq OWNED BY mocovi_costo_categoria.id_costo_categoria;


--
-- TOC entry 232 (class 1259 OID 44993)
-- Name: mocovi_credito; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE mocovi_credito (
    id_credito integer NOT NULL,
    id_periodo integer,
    id_unidad character(4),
    id_escalafon character(1),
    id_tipo_credito integer,
    descripcion character(4),
    credito numeric(10,3),
    id_programa integer
);


ALTER TABLE public.mocovi_credito OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 44991)
-- Name: mocovi_credito_id_credito_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE mocovi_credito_id_credito_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mocovi_credito_id_credito_seq OWNER TO postgres;

--
-- TOC entry 2377 (class 0 OID 0)
-- Dependencies: 231
-- Name: mocovi_credito_id_credito_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE mocovi_credito_id_credito_seq OWNED BY mocovi_credito.id_credito;


--
-- TOC entry 225 (class 1259 OID 44895)
-- Name: mocovi_periodo_presupuestario; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE mocovi_periodo_presupuestario (
    id_periodo integer NOT NULL,
    anio integer,
    fecha_inicio date,
    fecha_fin date,
    fecha_ultima_liquidacion date,
    actual boolean,
    id_liqui_ini integer,
    id_liqui_fin integer,
    id_liqui_1sac integer,
    id_liqui_2sac integer,
    presupuestando boolean,
    activo_para_carga_presupuestando boolean
);


ALTER TABLE public.mocovi_periodo_presupuestario OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 44893)
-- Name: mocovi_periodo_presupuestario_id_periodo_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE mocovi_periodo_presupuestario_id_periodo_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mocovi_periodo_presupuestario_id_periodo_seq OWNER TO postgres;

--
-- TOC entry 2378 (class 0 OID 0)
-- Dependencies: 224
-- Name: mocovi_periodo_presupuestario_id_periodo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE mocovi_periodo_presupuestario_id_periodo_seq OWNED BY mocovi_periodo_presupuestario.id_periodo;


--
-- TOC entry 230 (class 1259 OID 44938)
-- Name: mocovi_programa; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE mocovi_programa (
    id_programa integer NOT NULL,
    id_unidad character(4),
    id_tipo_programa integer,
    nombre character varying,
    area integer,
    sub_area integer
);


ALTER TABLE public.mocovi_programa OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 44936)
-- Name: mocovi_programa_id_programa_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE mocovi_programa_id_programa_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mocovi_programa_id_programa_seq OWNER TO postgres;

--
-- TOC entry 2379 (class 0 OID 0)
-- Dependencies: 229
-- Name: mocovi_programa_id_programa_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE mocovi_programa_id_programa_seq OWNED BY mocovi_programa.id_programa;


--
-- TOC entry 227 (class 1259 OID 44925)
-- Name: mocovi_tipo_credito; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE mocovi_tipo_credito (
    id_tipo_credito integer NOT NULL,
    tipo character(1)
);


ALTER TABLE public.mocovi_tipo_credito OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 44923)
-- Name: mocovi_tipo_credito_id_tipo_credito_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE mocovi_tipo_credito_id_tipo_credito_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mocovi_tipo_credito_id_tipo_credito_seq OWNER TO postgres;

--
-- TOC entry 2380 (class 0 OID 0)
-- Dependencies: 226
-- Name: mocovi_tipo_credito_id_tipo_credito_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE mocovi_tipo_credito_id_tipo_credito_seq OWNED BY mocovi_tipo_credito.id_tipo_credito;


--
-- TOC entry 221 (class 1259 OID 44855)
-- Name: mocovi_tipo_dependencia; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE mocovi_tipo_dependencia (
    id_tipo_dependencia integer NOT NULL,
    tipo character(60) NOT NULL
);


ALTER TABLE public.mocovi_tipo_dependencia OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 44853)
-- Name: mocovi_tipo_dependencia_id_tipo_dependencia_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE mocovi_tipo_dependencia_id_tipo_dependencia_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mocovi_tipo_dependencia_id_tipo_dependencia_seq OWNER TO postgres;

--
-- TOC entry 2381 (class 0 OID 0)
-- Dependencies: 220
-- Name: mocovi_tipo_dependencia_id_tipo_dependencia_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE mocovi_tipo_dependencia_id_tipo_dependencia_seq OWNED BY mocovi_tipo_dependencia.id_tipo_dependencia;


--
-- TOC entry 228 (class 1259 OID 44931)
-- Name: mocovi_tipo_programa; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE mocovi_tipo_programa (
    id_tipo_programa integer NOT NULL,
    tipo character varying
);


ALTER TABLE public.mocovi_tipo_programa OWNER TO postgres;

--
-- TOC entry 215 (class 1259 OID 35772)
-- Name: modulo; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE modulo (
    id_modulo integer NOT NULL,
    descripcion character(15)
);


ALTER TABLE public.modulo OWNER TO postgres;

--
-- TOC entry 187 (class 1259 OID 34306)
-- Name: norma; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE norma (
    id_norma integer NOT NULL,
    nro_norma integer,
    tipo_norma character(5),
    emite_norma character(4),
    fecha date,
    pdf bytea
);


ALTER TABLE public.norma OWNER TO postgres;

--
-- TOC entry 186 (class 1259 OID 34304)
-- Name: norma_id_norma_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE norma_id_norma_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.norma_id_norma_seq OWNER TO postgres;

--
-- TOC entry 2382 (class 0 OID 0)
-- Dependencies: 186
-- Name: norma_id_norma_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE norma_id_norma_seq OWNED BY norma.id_norma;


--
-- TOC entry 194 (class 1259 OID 34488)
-- Name: novedad; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE novedad (
    id_novedad integer NOT NULL,
    tipo_nov integer,
    desde date,
    hasta date,
    id_designacion integer,
    tipo_norma character(5),
    tipo_emite character(4),
    norma_legal character(10),
    observaciones character(30)
);


ALTER TABLE public.novedad OWNER TO postgres;

--
-- TOC entry 177 (class 1259 OID 34207)
-- Name: orientacion; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE orientacion (
    idorient integer NOT NULL,
    idarea integer NOT NULL,
    descripcion character(80) DEFAULT ''::bpchar NOT NULL
);


ALTER TABLE public.orientacion OWNER TO postgres;

--
-- TOC entry 178 (class 1259 OID 34218)
-- Name: pais; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pais (
    codigo_pais character(2) NOT NULL,
    nombre character varying(40)
);


ALTER TABLE public.pais OWNER TO postgres;

--
-- TOC entry 164 (class 1259 OID 34107)
-- Name: periodo; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE periodo (
    id_periodo integer NOT NULL,
    descripcion character(6) NOT NULL
);


ALTER TABLE public.periodo OWNER TO postgres;

--
-- TOC entry 163 (class 1259 OID 34105)
-- Name: periodo_id_periodo_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE periodo_id_periodo_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.periodo_id_periodo_seq OWNER TO postgres;

--
-- TOC entry 2383 (class 0 OID 0)
-- Dependencies: 163
-- Name: periodo_id_periodo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE periodo_id_periodo_seq OWNED BY periodo.id_periodo;


--
-- TOC entry 211 (class 1259 OID 34748)
-- Name: pextension; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pextension (
    id_pext integer NOT NULL,
    codigo integer,
    denominacion character(100),
    nro_resol character(20),
    fecha_resol date,
    emite_tipo character(4),
    uni_acad character(5),
    fec_desde date,
    fec_hasta date
);


ALTER TABLE public.pextension OWNER TO postgres;

--
-- TOC entry 210 (class 1259 OID 34746)
-- Name: pextension_id_pext_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pextension_id_pext_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pextension_id_pext_seq OWNER TO postgres;

--
-- TOC entry 2384 (class 0 OID 0)
-- Dependencies: 210
-- Name: pextension_id_pext_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pextension_id_pext_seq OWNED BY pextension.id_pext;


--
-- TOC entry 206 (class 1259 OID 34640)
-- Name: pinvestigacion; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE pinvestigacion (
    id_pinv integer NOT NULL,
    codigo integer,
    denominacion character(100),
    nro_resol character(20),
    fec_resol date,
    tipo_emite character(4),
    uni_acad character(5),
    fec_desde date,
    fec_hasta date
);


ALTER TABLE public.pinvestigacion OWNER TO postgres;

--
-- TOC entry 205 (class 1259 OID 34638)
-- Name: pinvestigacion_id_pinv_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE pinvestigacion_id_pinv_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pinvestigacion_id_pinv_seq OWNER TO postgres;

--
-- TOC entry 2385 (class 0 OID 0)
-- Dependencies: 205
-- Name: pinvestigacion_id_pinv_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE pinvestigacion_id_pinv_seq OWNED BY pinvestigacion.id_pinv;


--
-- TOC entry 196 (class 1259 OID 34515)
-- Name: plan_estudio; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE plan_estudio (
    id_plan integer NOT NULL,
    cod_plan character(10),
    cod_carrera character(11),
    desc_carrera character(120),
    titulo character(160),
    uni_acad character(5),
    ordenanza character(30)
);


ALTER TABLE public.plan_estudio OWNER TO postgres;

--
-- TOC entry 195 (class 1259 OID 34513)
-- Name: plan_estudio_id_plan_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE plan_estudio_id_plan_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.plan_estudio_id_plan_seq OWNER TO postgres;

--
-- TOC entry 2386 (class 0 OID 0)
-- Dependencies: 195
-- Name: plan_estudio_id_plan_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE plan_estudio_id_plan_seq OWNED BY plan_estudio.id_plan;


--
-- TOC entry 179 (class 1259 OID 34223)
-- Name: provincia; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE provincia (
    descripcion_pcia character(40),
    cod_pais character(2),
    codigo_pcia integer NOT NULL
);


ALTER TABLE public.provincia OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 46031)
-- Name: reserva; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE reserva (
    id_reserva integer NOT NULL,
    descripcion character varying
);


ALTER TABLE public.reserva OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 46029)
-- Name: reserva_id_reserva_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE reserva_id_reserva_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.reserva_id_reserva_seq OWNER TO postgres;

--
-- TOC entry 2387 (class 0 OID 0)
-- Dependencies: 237
-- Name: reserva_id_reserva_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE reserva_id_reserva_seq OWNED BY reserva.id_reserva;


--
-- TOC entry 161 (class 1259 OID 34090)
-- Name: tipo; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE tipo (
    nro_tabla integer NOT NULL,
    desc_abrev character(4) NOT NULL,
    desc_item character(30)
);


ALTER TABLE public.tipo OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 46020)
-- Name: tipo_designacion; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE tipo_designacion (
    id integer NOT NULL,
    descripcion character varying
);


ALTER TABLE public.tipo_designacion OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 46018)
-- Name: tipo_designacion_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE tipo_designacion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tipo_designacion_id_seq OWNER TO postgres;

--
-- TOC entry 2388 (class 0 OID 0)
-- Dependencies: 235
-- Name: tipo_designacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE tipo_designacion_id_seq OWNED BY tipo_designacion.id;


--
-- TOC entry 184 (class 1259 OID 34294)
-- Name: tipo_emite; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE tipo_emite (
    cod_emite character(4) NOT NULL,
    quien_emite_norma character(20) NOT NULL
);


ALTER TABLE public.tipo_emite OWNER TO postgres;

--
-- TOC entry 185 (class 1259 OID 34299)
-- Name: tipo_norma_exp; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE tipo_norma_exp (
    cod_tipo character(5) NOT NULL,
    nombre_tipo character(20) NOT NULL
);


ALTER TABLE public.tipo_norma_exp OWNER TO postgres;

--
-- TOC entry 193 (class 1259 OID 34463)
-- Name: tipo_novedad; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE tipo_novedad (
    id_tipo integer NOT NULL,
    desc_corta character(4),
    descripcion character(60)
);


ALTER TABLE public.tipo_novedad OWNER TO postgres;

--
-- TOC entry 182 (class 1259 OID 34263)
-- Name: titulo; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE titulo (
    codc_titul character(4) NOT NULL,
    nro_tab3 integer,
    codc_nivel character(4),
    desc_titul character(200)
);


ALTER TABLE public.titulo OWNER TO postgres;

--
-- TOC entry 183 (class 1259 OID 34278)
-- Name: titulos_docente; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE titulos_docente (
    id_docente integer NOT NULL,
    codc_titul character(4) NOT NULL,
    fec_emisi date,
    fec_finalizacion date,
    codc_entot character(4)
);


ALTER TABLE public.titulos_docente OWNER TO postgres;

--
-- TOC entry 162 (class 1259 OID 34095)
-- Name: unidad_acad; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE unidad_acad (
    sigla character(5) NOT NULL,
    descripcion character(60) NOT NULL,
    nro_tab6 integer,
    cod_regional character(4),
    id_tipo_dependencia integer
);


ALTER TABLE public.unidad_acad OWNER TO postgres;

--
-- TOC entry 214 (class 1259 OID 34814)
-- Name: vinculo; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE vinculo (
    cargo integer NOT NULL,
    vinc integer
);


ALTER TABLE public.vinculo OWNER TO postgres;

--
-- TOC entry 2129 (class 2604 OID 34175)
-- Name: id_di; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dedicacion_incentivo ALTER COLUMN id_di SET DEFAULT nextval('dedicacion_incentivo_id_di_seq'::regclass);


--
-- TOC entry 2137 (class 2604 OID 34356)
-- Name: id_designacion; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion ALTER COLUMN id_designacion SET DEFAULT nextval('designacion_id_designacion_seq'::regclass);


--
-- TOC entry 2150 (class 2604 OID 45067)
-- Name: id_designacion; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacionh ALTER COLUMN id_designacion SET DEFAULT nextval('designacionh_id_designacion_seq'::regclass);


--
-- TOC entry 2140 (class 2604 OID 34569)
-- Name: id_conjunto; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY en_conjunto ALTER COLUMN id_conjunto SET DEFAULT nextval('en_conjunto_id_conjunto_seq'::regclass);


--
-- TOC entry 2136 (class 2604 OID 34330)
-- Name: id_exp; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY expediente ALTER COLUMN id_exp SET DEFAULT nextval('expediente_id_exp_seq'::regclass);


--
-- TOC entry 2143 (class 2604 OID 44749)
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY impresion_540 ALTER COLUMN id SET DEFAULT nextval('impresion_540_id_seq'::regclass);


--
-- TOC entry 2127 (class 2604 OID 34166)
-- Name: id_inc; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY incentivo ALTER COLUMN id_inc SET DEFAULT nextval('incentivo_id_inc_seq'::regclass);


--
-- TOC entry 2139 (class 2604 OID 34531)
-- Name: id_materia; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY materia ALTER COLUMN id_materia SET DEFAULT nextval('materia_id_materia_seq'::regclass);


--
-- TOC entry 2145 (class 2604 OID 44885)
-- Name: id_costo_categoria; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_costo_categoria ALTER COLUMN id_costo_categoria SET DEFAULT nextval('mocovi_costo_categoria_id_costo_categoria_seq'::regclass);


--
-- TOC entry 2149 (class 2604 OID 44996)
-- Name: id_credito; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_credito ALTER COLUMN id_credito SET DEFAULT nextval('mocovi_credito_id_credito_seq'::regclass);


--
-- TOC entry 2146 (class 2604 OID 44898)
-- Name: id_periodo; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_periodo_presupuestario ALTER COLUMN id_periodo SET DEFAULT nextval('mocovi_periodo_presupuestario_id_periodo_seq'::regclass);


--
-- TOC entry 2148 (class 2604 OID 44941)
-- Name: id_programa; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_programa ALTER COLUMN id_programa SET DEFAULT nextval('mocovi_programa_id_programa_seq'::regclass);


--
-- TOC entry 2147 (class 2604 OID 44928)
-- Name: id_tipo_credito; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_tipo_credito ALTER COLUMN id_tipo_credito SET DEFAULT nextval('mocovi_tipo_credito_id_tipo_credito_seq'::regclass);


--
-- TOC entry 2144 (class 2604 OID 44858)
-- Name: id_tipo_dependencia; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_tipo_dependencia ALTER COLUMN id_tipo_dependencia SET DEFAULT nextval('mocovi_tipo_dependencia_id_tipo_dependencia_seq'::regclass);


--
-- TOC entry 2135 (class 2604 OID 34309)
-- Name: id_norma; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY norma ALTER COLUMN id_norma SET DEFAULT nextval('norma_id_norma_seq'::regclass);


--
-- TOC entry 2118 (class 2604 OID 34110)
-- Name: id_periodo; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY periodo ALTER COLUMN id_periodo SET DEFAULT nextval('periodo_id_periodo_seq'::regclass);


--
-- TOC entry 2142 (class 2604 OID 34751)
-- Name: id_pext; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY pextension ALTER COLUMN id_pext SET DEFAULT nextval('pextension_id_pext_seq'::regclass);


--
-- TOC entry 2141 (class 2604 OID 34643)
-- Name: id_pinv; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY pinvestigacion ALTER COLUMN id_pinv SET DEFAULT nextval('pinvestigacion_id_pinv_seq'::regclass);


--
-- TOC entry 2138 (class 2604 OID 34518)
-- Name: id_plan; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY plan_estudio ALTER COLUMN id_plan SET DEFAULT nextval('plan_estudio_id_plan_seq'::regclass);


--
-- TOC entry 2152 (class 2604 OID 46034)
-- Name: id_reserva; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY reserva ALTER COLUMN id_reserva SET DEFAULT nextval('reserva_id_reserva_seq'::regclass);


--
-- TOC entry 2151 (class 2604 OID 46023)
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY tipo_designacion ALTER COLUMN id SET DEFAULT nextval('tipo_designacion_id_seq'::regclass);


--
-- TOC entry 2178 (class 2606 OID 34201)
-- Name: pk_area; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY area
    ADD CONSTRAINT pk_area PRIMARY KEY (idarea);


--
-- TOC entry 2218 (class 2606 OID 36411)
-- Name: pk_asignacion_materia; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY asignacion_materia
    ADD CONSTRAINT pk_asignacion_materia PRIMARY KEY (id_designacion, id_materia, modulo);


--
-- TOC entry 2168 (class 2606 OID 34160)
-- Name: pk_caracter; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY caracter
    ADD CONSTRAINT pk_caracter PRIMARY KEY (id_car);


--
-- TOC entry 2160 (class 2606 OID 34119)
-- Name: pk_categ_estatuto; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY categ_estatuto
    ADD CONSTRAINT pk_categ_estatuto PRIMARY KEY (codigo_est);


--
-- TOC entry 2220 (class 2606 OID 34632)
-- Name: pk_categoria_invest; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY categoria_invest
    ADD CONSTRAINT pk_categoria_invest PRIMARY KEY (cod_cati);


--
-- TOC entry 2162 (class 2606 OID 34126)
-- Name: pk_categsiu; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY categ_siu
    ADD CONSTRAINT pk_categsiu PRIMARY KEY (codigo_siu);


--
-- TOC entry 2174 (class 2606 OID 34184)
-- Name: pk_cic_conicef; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cic_conicef
    ADD CONSTRAINT pk_cic_conicef PRIMARY KEY (id);


--
-- TOC entry 2164 (class 2606 OID 34132)
-- Name: pk_dedicacion; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY dedicacion
    ADD CONSTRAINT pk_dedicacion PRIMARY KEY (id_ded);


--
-- TOC entry 2172 (class 2606 OID 34178)
-- Name: pk_dedicacion_incentivo; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY dedicacion_incentivo
    ADD CONSTRAINT pk_dedicacion_incentivo PRIMARY KEY (id_di);


--
-- TOC entry 2176 (class 2606 OID 34190)
-- Name: pk_departamento; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY departamento
    ADD CONSTRAINT pk_departamento PRIMARY KEY (iddepto);


--
-- TOC entry 2202 (class 2606 OID 34362)
-- Name: pk_designacion; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT pk_designacion PRIMARY KEY (id_designacion);


--
-- TOC entry 2186 (class 2606 OID 34237)
-- Name: pk_docente; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY docente
    ADD CONSTRAINT pk_docente PRIMARY KEY (id_docente);


--
-- TOC entry 2214 (class 2606 OID 34571)
-- Name: pk_enconjunto; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY en_conjunto
    ADD CONSTRAINT pk_enconjunto PRIMARY KEY (id_conjunto, ua, id_materia);


--
-- TOC entry 2188 (class 2606 OID 34257)
-- Name: pk_entid_otorg; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY entidad_otorgante
    ADD CONSTRAINT pk_entid_otorg PRIMARY KEY (cod_entidad);


--
-- TOC entry 2246 (class 2606 OID 44836)
-- Name: pk_escalafon; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY escalafon
    ADD CONSTRAINT pk_escalafon PRIMARY KEY (id_escalafon);


--
-- TOC entry 2200 (class 2606 OID 34335)
-- Name: pk_expediente; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY expediente
    ADD CONSTRAINT pk_expediente PRIMARY KEY (id_exp);


--
-- TOC entry 2230 (class 2606 OID 34745)
-- Name: pk_funcion_extension; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY funcion_extension
    ADD CONSTRAINT pk_funcion_extension PRIMARY KEY (id_extension);


--
-- TOC entry 2222 (class 2606 OID 34637)
-- Name: pk_funcion_investigador; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY funcion_investigador
    ADD CONSTRAINT pk_funcion_investigador PRIMARY KEY (id_funcion);


--
-- TOC entry 2244 (class 2606 OID 44751)
-- Name: pk_impresion_540; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY impresion_540
    ADD CONSTRAINT pk_impresion_540 PRIMARY KEY (id);


--
-- TOC entry 2204 (class 2606 OID 45061)
-- Name: pk_imputacion; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY imputacion
    ADD CONSTRAINT pk_imputacion PRIMARY KEY (id_designacion, id_programa);


--
-- TOC entry 2170 (class 2606 OID 34169)
-- Name: pk_incentivo; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY incentivo
    ADD CONSTRAINT pk_incentivo PRIMARY KEY (id_inc);


--
-- TOC entry 2216 (class 2606 OID 34586)
-- Name: pk_inscriptos; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY inscriptos
    ADD CONSTRAINT pk_inscriptos PRIMARY KEY (anio_acad, id_materia);


--
-- TOC entry 2228 (class 2606 OID 34710)
-- Name: pk_integrante_externo; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY integrante_externo_pi
    ADD CONSTRAINT pk_integrante_externo PRIMARY KEY (tipo_docum, nro_docum, pinvest);


--
-- TOC entry 2236 (class 2606 OID 34788)
-- Name: pk_integrante_externo_pe; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY integrante_externo_pe
    ADD CONSTRAINT pk_integrante_externo_pe PRIMARY KEY (tipo_docum, nro_docum, id_pext);


--
-- TOC entry 2226 (class 2606 OID 34660)
-- Name: pk_integrante_interno; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY integrante_interno_pi
    ADD CONSTRAINT pk_integrante_interno PRIMARY KEY (id_docente, pinvest);


--
-- TOC entry 2234 (class 2606 OID 34768)
-- Name: pk_integrante_interno_pe; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY integrante_interno_pe
    ADD CONSTRAINT pk_integrante_interno_pe PRIMARY KEY (id_docente, id_pext);


--
-- TOC entry 2242 (class 2606 OID 36675)
-- Name: pk_localidad; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY localidad
    ADD CONSTRAINT pk_localidad PRIMARY KEY (id);


--
-- TOC entry 2166 (class 2606 OID 34139)
-- Name: pk_macheo_categ; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY macheo_categ
    ADD CONSTRAINT pk_macheo_categ PRIMARY KEY (catest, id_ded);


--
-- TOC entry 2212 (class 2606 OID 34533)
-- Name: pk_materia; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY materia
    ADD CONSTRAINT pk_materia PRIMARY KEY (id_materia);


--
-- TOC entry 2250 (class 2606 OID 44887)
-- Name: pk_mocovi_costo_categoria; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY mocovi_costo_categoria
    ADD CONSTRAINT pk_mocovi_costo_categoria PRIMARY KEY (id_costo_categoria);


--
-- TOC entry 2260 (class 2606 OID 44998)
-- Name: pk_mocovi_credito; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY mocovi_credito
    ADD CONSTRAINT pk_mocovi_credito PRIMARY KEY (id_credito);


--
-- TOC entry 2258 (class 2606 OID 44946)
-- Name: pk_mocovi_programa; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY mocovi_programa
    ADD CONSTRAINT pk_mocovi_programa PRIMARY KEY (id_programa);


--
-- TOC entry 2254 (class 2606 OID 44930)
-- Name: pk_mocovi_tipo_credito; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY mocovi_tipo_credito
    ADD CONSTRAINT pk_mocovi_tipo_credito PRIMARY KEY (id_tipo_credito);


--
-- TOC entry 2256 (class 2606 OID 44935)
-- Name: pk_mocovi_tipo_programa; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY mocovi_tipo_programa
    ADD CONSTRAINT pk_mocovi_tipo_programa PRIMARY KEY (id_tipo_programa);


--
-- TOC entry 2240 (class 2606 OID 35776)
-- Name: pk_modulo; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY modulo
    ADD CONSTRAINT pk_modulo PRIMARY KEY (id_modulo);


--
-- TOC entry 2198 (class 2606 OID 34314)
-- Name: pk_norma; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY norma
    ADD CONSTRAINT pk_norma PRIMARY KEY (id_norma);


--
-- TOC entry 2208 (class 2606 OID 34492)
-- Name: pk_novedad; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY novedad
    ADD CONSTRAINT pk_novedad PRIMARY KEY (id_novedad);


--
-- TOC entry 2180 (class 2606 OID 36134)
-- Name: pk_orientacion; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY orientacion
    ADD CONSTRAINT pk_orientacion PRIMARY KEY (idorient, idarea);


--
-- TOC entry 2182 (class 2606 OID 34222)
-- Name: pk_pais; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pais
    ADD CONSTRAINT pk_pais PRIMARY KEY (codigo_pais);


--
-- TOC entry 2158 (class 2606 OID 34112)
-- Name: pk_periodo; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY periodo
    ADD CONSTRAINT pk_periodo PRIMARY KEY (id_periodo);


--
-- TOC entry 2252 (class 2606 OID 44900)
-- Name: pk_periodo_presupuestario; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY mocovi_periodo_presupuestario
    ADD CONSTRAINT pk_periodo_presupuestario PRIMARY KEY (id_periodo);


--
-- TOC entry 2232 (class 2606 OID 34753)
-- Name: pk_pextension; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pextension
    ADD CONSTRAINT pk_pextension PRIMARY KEY (id_pext);


--
-- TOC entry 2224 (class 2606 OID 34645)
-- Name: pk_pinvestigacion; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY pinvestigacion
    ADD CONSTRAINT pk_pinvestigacion PRIMARY KEY (id_pinv);


--
-- TOC entry 2210 (class 2606 OID 34520)
-- Name: pk_plan_estudios; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY plan_estudio
    ADD CONSTRAINT pk_plan_estudios PRIMARY KEY (id_plan);


--
-- TOC entry 2184 (class 2606 OID 36540)
-- Name: pk_provincia; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY provincia
    ADD CONSTRAINT pk_provincia PRIMARY KEY (codigo_pcia);


--
-- TOC entry 2264 (class 2606 OID 46039)
-- Name: pk_reserva; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY reserva
    ADD CONSTRAINT pk_reserva PRIMARY KEY (id_reserva);


--
-- TOC entry 2154 (class 2606 OID 34094)
-- Name: pk_tipo; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY tipo
    ADD CONSTRAINT pk_tipo PRIMARY KEY (nro_tabla, desc_abrev);


--
-- TOC entry 2248 (class 2606 OID 44860)
-- Name: pk_tipo_depend; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY mocovi_tipo_dependencia
    ADD CONSTRAINT pk_tipo_depend PRIMARY KEY (id_tipo_dependencia);


--
-- TOC entry 2262 (class 2606 OID 46028)
-- Name: pk_tipo_designacion; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY tipo_designacion
    ADD CONSTRAINT pk_tipo_designacion PRIMARY KEY (id);


--
-- TOC entry 2194 (class 2606 OID 34298)
-- Name: pk_tipo_emite; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY tipo_emite
    ADD CONSTRAINT pk_tipo_emite PRIMARY KEY (cod_emite);


--
-- TOC entry 2196 (class 2606 OID 34303)
-- Name: pk_tipo_norma_exp; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY tipo_norma_exp
    ADD CONSTRAINT pk_tipo_norma_exp PRIMARY KEY (cod_tipo);


--
-- TOC entry 2206 (class 2606 OID 34467)
-- Name: pk_tipo_novedad; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY tipo_novedad
    ADD CONSTRAINT pk_tipo_novedad PRIMARY KEY (id_tipo);


--
-- TOC entry 2192 (class 2606 OID 34282)
-- Name: pk_tit_doce; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY titulos_docente
    ADD CONSTRAINT pk_tit_doce PRIMARY KEY (id_docente, codc_titul);


--
-- TOC entry 2190 (class 2606 OID 34267)
-- Name: pk_titulo; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY titulo
    ADD CONSTRAINT pk_titulo PRIMARY KEY (codc_titul);


--
-- TOC entry 2156 (class 2606 OID 34099)
-- Name: pk_ua; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY unidad_acad
    ADD CONSTRAINT pk_ua PRIMARY KEY (sigla);


--
-- TOC entry 2238 (class 2606 OID 34818)
-- Name: pk_vinculo; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY vinculo
    ADD CONSTRAINT pk_vinculo PRIMARY KEY (cargo);


--
-- TOC entry 2272 (class 2606 OID 45129)
-- Name: fk_area_departamento; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY area
    ADD CONSTRAINT fk_area_departamento FOREIGN KEY (iddepto) REFERENCES departamento(iddepto);


--
-- TOC entry 2319 (class 2606 OID 45268)
-- Name: fk_asigmateria_designacion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY asignacion_materia
    ADD CONSTRAINT fk_asigmateria_designacion FOREIGN KEY (id_designacion) REFERENCES designacion(id_designacion);


--
-- TOC entry 2320 (class 2606 OID 45273)
-- Name: fk_asigmateria_materia; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY asignacion_materia
    ADD CONSTRAINT fk_asigmateria_materia FOREIGN KEY (id_materia) REFERENCES materia(id_materia);


--
-- TOC entry 2321 (class 2606 OID 45278)
-- Name: fk_asigmateria_modulo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY asignacion_materia
    ADD CONSTRAINT fk_asigmateria_modulo FOREIGN KEY (modulo) REFERENCES modulo(id_modulo);


--
-- TOC entry 2322 (class 2606 OID 45283)
-- Name: fk_asigmateria_periodo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY asignacion_materia
    ADD CONSTRAINT fk_asigmateria_periodo FOREIGN KEY (id_periodo) REFERENCES periodo(id_periodo);


--
-- TOC entry 2323 (class 2606 OID 45288)
-- Name: fk_asigmateria_tipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY asignacion_materia
    ADD CONSTRAINT fk_asigmateria_tipo FOREIGN KEY (nro_tab8, rol) REFERENCES tipo(nro_tabla, desc_abrev);


--
-- TOC entry 2267 (class 2606 OID 44837)
-- Name: fk_categ_siu_escalafon; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY categ_siu
    ADD CONSTRAINT fk_categ_siu_escalafon FOREIGN KEY (escalafon) REFERENCES escalafon(id_escalafon);


--
-- TOC entry 2351 (class 2606 OID 44903)
-- Name: fk_costo_categoria_periodo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_costo_categoria
    ADD CONSTRAINT fk_costo_categoria_periodo FOREIGN KEY (id_periodo) REFERENCES mocovi_periodo_presupuestario(id_periodo);


--
-- TOC entry 2352 (class 2606 OID 44908)
-- Name: fk_costo_categoria_siu; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_costo_categoria
    ADD CONSTRAINT fk_costo_categoria_siu FOREIGN KEY (codigo_siu) REFERENCES categ_siu(codigo_siu);


--
-- TOC entry 2357 (class 2606 OID 45009)
-- Name: fk_credito_escalafon; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_credito
    ADD CONSTRAINT fk_credito_escalafon FOREIGN KEY (id_escalafon) REFERENCES escalafon(id_escalafon);


--
-- TOC entry 2356 (class 2606 OID 44999)
-- Name: fk_credito_periodo_pres; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_credito
    ADD CONSTRAINT fk_credito_periodo_pres FOREIGN KEY (id_periodo) REFERENCES mocovi_periodo_presupuestario(id_periodo);


--
-- TOC entry 2359 (class 2606 OID 45019)
-- Name: fk_credito_programa; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_credito
    ADD CONSTRAINT fk_credito_programa FOREIGN KEY (id_programa) REFERENCES mocovi_programa(id_programa);


--
-- TOC entry 2358 (class 2606 OID 45014)
-- Name: fk_credito_tipo_credito; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_credito
    ADD CONSTRAINT fk_credito_tipo_credito FOREIGN KEY (id_tipo_credito) REFERENCES mocovi_tipo_credito(id_tipo_credito);


--
-- TOC entry 2355 (class 2606 OID 45068)
-- Name: fk_credito_unidad; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_credito
    ADD CONSTRAINT fk_credito_unidad FOREIGN KEY (id_unidad) REFERENCES unidad_acad(sigla) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2271 (class 2606 OID 45098)
-- Name: fk_departamento_ua; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY departamento
    ADD CONSTRAINT fk_departamento_ua FOREIGN KEY (idunidad_academica) REFERENCES unidad_acad(sigla);


--
-- TOC entry 2287 (class 2606 OID 46215)
-- Name: fk_designacion_540; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_540 FOREIGN KEY (nro_540) REFERENCES impresion_540(id);


--
-- TOC entry 2288 (class 2606 OID 46220)
-- Name: fk_designacion_caracter; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_caracter FOREIGN KEY (carac) REFERENCES caracter(id_car);


--
-- TOC entry 2289 (class 2606 OID 46225)
-- Name: fk_designacion_cargogestion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_cargogestion FOREIGN KEY (cargo_gestion) REFERENCES categ_siu(codigo_siu);


--
-- TOC entry 2290 (class 2606 OID 46230)
-- Name: fk_designacion_catest; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_catest FOREIGN KEY (cat_estat) REFERENCES categ_estatuto(codigo_est);


--
-- TOC entry 2291 (class 2606 OID 46235)
-- Name: fk_designacion_catsiu; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_catsiu FOREIGN KEY (cat_mapuche) REFERENCES categ_siu(codigo_siu);


--
-- TOC entry 2292 (class 2606 OID 46240)
-- Name: fk_designacion_ciconi; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_ciconi FOREIGN KEY (cic_con) REFERENCES cic_conicef(id);


--
-- TOC entry 2293 (class 2606 OID 46245)
-- Name: fk_designacion_dedicacion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_dedicacion FOREIGN KEY (dedic) REFERENCES dedicacion(id_ded);


--
-- TOC entry 2294 (class 2606 OID 46250)
-- Name: fk_designacion_dedincent; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_dedincent FOREIGN KEY (dedi_incen) REFERENCES dedicacion_incentivo(id_di);


--
-- TOC entry 2295 (class 2606 OID 46255)
-- Name: fk_designacion_departamento; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_departamento FOREIGN KEY (id_departamento) REFERENCES departamento(iddepto);


--
-- TOC entry 2296 (class 2606 OID 46260)
-- Name: fk_designacion_docente; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_docente FOREIGN KEY (id_docente) REFERENCES docente(id_docente);


--
-- TOC entry 2297 (class 2606 OID 46265)
-- Name: fk_designacion_emite; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_emite FOREIGN KEY (emite_cargo_gestion) REFERENCES tipo_emite(cod_emite);


--
-- TOC entry 2298 (class 2606 OID 46270)
-- Name: fk_designacion_expediente; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_expediente FOREIGN KEY (id_expediente) REFERENCES expediente(id_exp);


--
-- TOC entry 2299 (class 2606 OID 46275)
-- Name: fk_designacion_incentivo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_incentivo FOREIGN KEY (tipo_incentivo) REFERENCES incentivo(id_inc);


--
-- TOC entry 2300 (class 2606 OID 46280)
-- Name: fk_designacion_norma; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_norma FOREIGN KEY (id_norma) REFERENCES norma(id_norma);


--
-- TOC entry 2301 (class 2606 OID 46285)
-- Name: fk_designacion_orientacion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_orientacion FOREIGN KEY (id_orientacion, id_area) REFERENCES orientacion(idorient, idarea);


--
-- TOC entry 2302 (class 2606 OID 46290)
-- Name: fk_designacion_reserva; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_reserva FOREIGN KEY (id_reserva) REFERENCES reserva(id_reserva);


--
-- TOC entry 2303 (class 2606 OID 46295)
-- Name: fk_designacion_tipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_tipo FOREIGN KEY (tipo_desig) REFERENCES tipo_designacion(id);


--
-- TOC entry 2304 (class 2606 OID 46300)
-- Name: fk_designacion_ua; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY designacion
    ADD CONSTRAINT fk_designacion_ua FOREIGN KEY (uni_acad) REFERENCES unidad_acad(sigla);


--
-- TOC entry 2275 (class 2606 OID 36551)
-- Name: fk_docente_pais_nacim; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY docente
    ADD CONSTRAINT fk_docente_pais_nacim FOREIGN KEY (pais_nacim) REFERENCES pais(codigo_pais);


--
-- TOC entry 2277 (class 2606 OID 36561)
-- Name: fk_docente_pcia_nacim; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY docente
    ADD CONSTRAINT fk_docente_pcia_nacim FOREIGN KEY (pcia_nacim) REFERENCES provincia(codigo_pcia);


--
-- TOC entry 2276 (class 2606 OID 36556)
-- Name: fk_docente_tipodocum; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY docente
    ADD CONSTRAINT fk_docente_tipodocum FOREIGN KEY (nro_tabla, tipo_docum) REFERENCES tipo(nro_tabla, desc_abrev);


--
-- TOC entry 2316 (class 2606 OID 34572)
-- Name: fk_enconjunto_materia; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY en_conjunto
    ADD CONSTRAINT fk_enconjunto_materia FOREIGN KEY (id_materia) REFERENCES materia(id_materia);


--
-- TOC entry 2317 (class 2606 OID 34577)
-- Name: fk_enconjunto_ua; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY en_conjunto
    ADD CONSTRAINT fk_enconjunto_ua FOREIGN KEY (ua) REFERENCES unidad_acad(sigla);


--
-- TOC entry 2278 (class 2606 OID 44499)
-- Name: fk_ent_otorg_ciudad; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY entidad_otorgante
    ADD CONSTRAINT fk_ent_otorg_ciudad FOREIGN KEY (cod_ciudad) REFERENCES localidad(id);


--
-- TOC entry 2286 (class 2606 OID 34341)
-- Name: fk_expediente_emite; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY expediente
    ADD CONSTRAINT fk_expediente_emite FOREIGN KEY (emite_tipo) REFERENCES tipo_emite(cod_emite);


--
-- TOC entry 2285 (class 2606 OID 34336)
-- Name: fk_expediente_tipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY expediente
    ADD CONSTRAINT fk_expediente_tipo FOREIGN KEY (tipo_exp) REFERENCES tipo_norma_exp(cod_tipo);


--
-- TOC entry 2305 (class 2606 OID 45055)
-- Name: fk_imputacion_programa; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY imputacion
    ADD CONSTRAINT fk_imputacion_programa FOREIGN KEY (id_programa) REFERENCES mocovi_programa(id_programa);


--
-- TOC entry 2318 (class 2606 OID 34587)
-- Name: fk_inscriptos_materia; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY inscriptos
    ADD CONSTRAINT fk_inscriptos_materia FOREIGN KEY (id_materia) REFERENCES materia(id_materia);


--
-- TOC entry 2331 (class 2606 OID 36591)
-- Name: fk_integrante_ext_categ; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_externo_pi
    ADD CONSTRAINT fk_integrante_ext_categ FOREIGN KEY (cat_invest) REFERENCES categoria_invest(cod_cati);


--
-- TOC entry 2332 (class 2606 OID 36596)
-- Name: fk_integrante_ext_funcion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_externo_pi
    ADD CONSTRAINT fk_integrante_ext_funcion FOREIGN KEY (funcion_p) REFERENCES funcion_investigador(id_funcion);


--
-- TOC entry 2333 (class 2606 OID 36601)
-- Name: fk_integrante_ext_pais; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_externo_pi
    ADD CONSTRAINT fk_integrante_ext_pais FOREIGN KEY (pais_nacim) REFERENCES pais(codigo_pais);


--
-- TOC entry 2343 (class 2606 OID 36641)
-- Name: fk_integrante_ext_pais; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_externo_pe
    ADD CONSTRAINT fk_integrante_ext_pais FOREIGN KEY (pais_nacim) REFERENCES pais(codigo_pais);


--
-- TOC entry 2336 (class 2606 OID 36616)
-- Name: fk_integrante_ext_pcia; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_externo_pi
    ADD CONSTRAINT fk_integrante_ext_pcia FOREIGN KEY (pcia_nacim) REFERENCES provincia(codigo_pcia);


--
-- TOC entry 2347 (class 2606 OID 36661)
-- Name: fk_integrante_ext_pcia; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_externo_pe
    ADD CONSTRAINT fk_integrante_ext_pcia FOREIGN KEY (pcia_nacim) REFERENCES provincia(codigo_pcia);


--
-- TOC entry 2334 (class 2606 OID 36606)
-- Name: fk_integrante_ext_pinv; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_externo_pi
    ADD CONSTRAINT fk_integrante_ext_pinv FOREIGN KEY (pinvest) REFERENCES pinvestigacion(id_pinv);


--
-- TOC entry 2335 (class 2606 OID 36611)
-- Name: fk_integrante_ext_tipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_externo_pi
    ADD CONSTRAINT fk_integrante_ext_tipo FOREIGN KEY (nro_tabla, tipo_docum) REFERENCES tipo(nro_tabla, desc_abrev);


--
-- TOC entry 2344 (class 2606 OID 36646)
-- Name: fk_integrante_externo_pe_tipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_externo_pe
    ADD CONSTRAINT fk_integrante_externo_pe_tipo FOREIGN KEY (nro_tabla, tipo_docum) REFERENCES tipo(nro_tabla, desc_abrev);


--
-- TOC entry 2326 (class 2606 OID 35727)
-- Name: fk_integrante_int_categ; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_interno_pi
    ADD CONSTRAINT fk_integrante_int_categ FOREIGN KEY (cat_investigador) REFERENCES categoria_invest(cod_cati);


--
-- TOC entry 2327 (class 2606 OID 35732)
-- Name: fk_integrante_int_docente; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_interno_pi
    ADD CONSTRAINT fk_integrante_int_docente FOREIGN KEY (id_docente) REFERENCES docente(id_docente);


--
-- TOC entry 2328 (class 2606 OID 35737)
-- Name: fk_integrante_int_funcion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_interno_pi
    ADD CONSTRAINT fk_integrante_int_funcion FOREIGN KEY (funcion_p) REFERENCES funcion_investigador(id_funcion);


--
-- TOC entry 2329 (class 2606 OID 35742)
-- Name: fk_integrante_int_pinv; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_interno_pi
    ADD CONSTRAINT fk_integrante_int_pinv FOREIGN KEY (pinvest) REFERENCES pinvestigacion(id_pinv);


--
-- TOC entry 2330 (class 2606 OID 35747)
-- Name: fk_integrante_int_ua; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_interno_pi
    ADD CONSTRAINT fk_integrante_int_ua FOREIGN KEY (ua) REFERENCES unidad_acad(sigla);


--
-- TOC entry 2339 (class 2606 OID 35802)
-- Name: fk_integrante_oe_funcion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_interno_pe
    ADD CONSTRAINT fk_integrante_oe_funcion FOREIGN KEY (funcion_p) REFERENCES funcion_extension(id_extension);


--
-- TOC entry 2340 (class 2606 OID 35807)
-- Name: fk_integrante_pe_docente; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_interno_pe
    ADD CONSTRAINT fk_integrante_pe_docente FOREIGN KEY (id_docente) REFERENCES docente(id_docente);


--
-- TOC entry 2345 (class 2606 OID 36651)
-- Name: fk_integrante_pe_funcion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_externo_pe
    ADD CONSTRAINT fk_integrante_pe_funcion FOREIGN KEY (funcion_p) REFERENCES funcion_extension(id_extension);


--
-- TOC entry 2341 (class 2606 OID 35812)
-- Name: fk_integrante_pe_pinv; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_interno_pe
    ADD CONSTRAINT fk_integrante_pe_pinv FOREIGN KEY (id_pext) REFERENCES pextension(id_pext);


--
-- TOC entry 2346 (class 2606 OID 36656)
-- Name: fk_integrante_pe_pinv; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_externo_pe
    ADD CONSTRAINT fk_integrante_pe_pinv FOREIGN KEY (id_pext) REFERENCES pextension(id_pext);


--
-- TOC entry 2342 (class 2606 OID 35817)
-- Name: fk_integrante_pe_ua; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY integrante_interno_pe
    ADD CONSTRAINT fk_integrante_pe_ua FOREIGN KEY (ua) REFERENCES unidad_acad(sigla);


--
-- TOC entry 2350 (class 2606 OID 36676)
-- Name: fk_localidad_pcia; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY localidad
    ADD CONSTRAINT fk_localidad_pcia FOREIGN KEY (id_provincia) REFERENCES provincia(codigo_pcia);


--
-- TOC entry 2270 (class 2606 OID 34150)
-- Name: fk_macheo_categ_ded; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY macheo_categ
    ADD CONSTRAINT fk_macheo_categ_ded FOREIGN KEY (id_ded) REFERENCES dedicacion(id_ded);


--
-- TOC entry 2268 (class 2606 OID 34140)
-- Name: fk_macheo_categ_est; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY macheo_categ
    ADD CONSTRAINT fk_macheo_categ_est FOREIGN KEY (catest) REFERENCES categ_estatuto(codigo_est);


--
-- TOC entry 2269 (class 2606 OID 34145)
-- Name: fk_macheo_categ_siu; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY macheo_categ
    ADD CONSTRAINT fk_macheo_categ_siu FOREIGN KEY (catsiu) REFERENCES categ_siu(codigo_siu);


--
-- TOC entry 2311 (class 2606 OID 36156)
-- Name: fk_materia_departamento; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY materia
    ADD CONSTRAINT fk_materia_departamento FOREIGN KEY (id_departamento) REFERENCES departamento(iddepto);


--
-- TOC entry 2315 (class 2606 OID 36176)
-- Name: fk_materia_orientacion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY materia
    ADD CONSTRAINT fk_materia_orientacion FOREIGN KEY (id_orientacion, id_area) REFERENCES orientacion(idorient, idarea);


--
-- TOC entry 2312 (class 2606 OID 36161)
-- Name: fk_materia_periodo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY materia
    ADD CONSTRAINT fk_materia_periodo FOREIGN KEY (periodo_dictado) REFERENCES periodo(id_periodo);


--
-- TOC entry 2313 (class 2606 OID 36166)
-- Name: fk_materia_periodoreal; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY materia
    ADD CONSTRAINT fk_materia_periodoreal FOREIGN KEY (periodo_dictado_real) REFERENCES periodo(id_periodo);


--
-- TOC entry 2314 (class 2606 OID 36171)
-- Name: fk_materia_plan; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY materia
    ADD CONSTRAINT fk_materia_plan FOREIGN KEY (id_plan) REFERENCES plan_estudio(id_plan);


--
-- TOC entry 2284 (class 2606 OID 34320)
-- Name: fk_norma_emite; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY norma
    ADD CONSTRAINT fk_norma_emite FOREIGN KEY (emite_norma) REFERENCES tipo_emite(cod_emite);


--
-- TOC entry 2283 (class 2606 OID 34315)
-- Name: fk_norma_tipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY norma
    ADD CONSTRAINT fk_norma_tipo FOREIGN KEY (tipo_norma) REFERENCES tipo_norma_exp(cod_tipo);


--
-- TOC entry 2306 (class 2606 OID 34493)
-- Name: fk_novedad_designacion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY novedad
    ADD CONSTRAINT fk_novedad_designacion FOREIGN KEY (id_designacion) REFERENCES designacion(id_designacion);


--
-- TOC entry 2308 (class 2606 OID 34503)
-- Name: fk_novedad_emite; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY novedad
    ADD CONSTRAINT fk_novedad_emite FOREIGN KEY (tipo_emite) REFERENCES tipo_emite(cod_emite);


--
-- TOC entry 2307 (class 2606 OID 34498)
-- Name: fk_novedad_norma; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY novedad
    ADD CONSTRAINT fk_novedad_norma FOREIGN KEY (tipo_norma) REFERENCES tipo_norma_exp(cod_tipo);


--
-- TOC entry 2309 (class 2606 OID 34508)
-- Name: fk_novedad_tipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY novedad
    ADD CONSTRAINT fk_novedad_tipo FOREIGN KEY (tipo_nov) REFERENCES tipo_novedad(id_tipo);


--
-- TOC entry 2273 (class 2606 OID 36128)
-- Name: fk_orientacion_area; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY orientacion
    ADD CONSTRAINT fk_orientacion_area FOREIGN KEY (idarea) REFERENCES area(idarea);


--
-- TOC entry 2338 (class 2606 OID 34759)
-- Name: fk_pextension_ua; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY pextension
    ADD CONSTRAINT fk_pextension_ua FOREIGN KEY (uni_acad) REFERENCES unidad_acad(sigla);


--
-- TOC entry 2324 (class 2606 OID 34646)
-- Name: fk_pinvestigacion_emitetipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY pinvestigacion
    ADD CONSTRAINT fk_pinvestigacion_emitetipo FOREIGN KEY (tipo_emite) REFERENCES tipo_emite(cod_emite);


--
-- TOC entry 2325 (class 2606 OID 34651)
-- Name: fk_pinvestigacion_ua; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY pinvestigacion
    ADD CONSTRAINT fk_pinvestigacion_ua FOREIGN KEY (uni_acad) REFERENCES unidad_acad(sigla);


--
-- TOC entry 2310 (class 2606 OID 34521)
-- Name: fk_plan_ua; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY plan_estudio
    ADD CONSTRAINT fk_plan_ua FOREIGN KEY (uni_acad) REFERENCES unidad_acad(sigla);


--
-- TOC entry 2337 (class 2606 OID 34754)
-- Name: fk_ppextension_emitetipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY pextension
    ADD CONSTRAINT fk_ppextension_emitetipo FOREIGN KEY (emite_tipo) REFERENCES tipo_emite(cod_emite);


--
-- TOC entry 2353 (class 2606 OID 45078)
-- Name: fk_programa_tipo_programa; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_programa
    ADD CONSTRAINT fk_programa_tipo_programa FOREIGN KEY (id_tipo_programa) REFERENCES mocovi_tipo_programa(id_tipo_programa) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2354 (class 2606 OID 45073)
-- Name: fk_programa_uni_acad; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY mocovi_programa
    ADD CONSTRAINT fk_programa_uni_acad FOREIGN KEY (id_unidad) REFERENCES unidad_acad(sigla) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 2274 (class 2606 OID 36534)
-- Name: fk_provincia_pais; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY provincia
    ADD CONSTRAINT fk_provincia_pais FOREIGN KEY (cod_pais) REFERENCES pais(codigo_pais);


--
-- TOC entry 2282 (class 2606 OID 36018)
-- Name: fk_titdoce_entidad; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY titulos_docente
    ADD CONSTRAINT fk_titdoce_entidad FOREIGN KEY (codc_entot) REFERENCES entidad_otorgante(cod_entidad);


--
-- TOC entry 2280 (class 2606 OID 36008)
-- Name: fk_titdoce_leg; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY titulos_docente
    ADD CONSTRAINT fk_titdoce_leg FOREIGN KEY (id_docente) REFERENCES docente(id_docente);


--
-- TOC entry 2281 (class 2606 OID 36013)
-- Name: fk_titdoce_tit; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY titulos_docente
    ADD CONSTRAINT fk_titdoce_tit FOREIGN KEY (codc_titul) REFERENCES titulo(codc_titul);


--
-- TOC entry 2279 (class 2606 OID 36003)
-- Name: fk_titulo_tipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY titulo
    ADD CONSTRAINT fk_titulo_tipo FOREIGN KEY (nro_tab3, codc_nivel) REFERENCES tipo(nro_tabla, desc_abrev);


--
-- TOC entry 2265 (class 2606 OID 44913)
-- Name: fk_ua_tipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY unidad_acad
    ADD CONSTRAINT fk_ua_tipo FOREIGN KEY (nro_tab6, cod_regional) REFERENCES tipo(nro_tabla, desc_abrev);


--
-- TOC entry 2266 (class 2606 OID 44918)
-- Name: fk_ua_tipo_dep; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY unidad_acad
    ADD CONSTRAINT fk_ua_tipo_dep FOREIGN KEY (id_tipo_dependencia) REFERENCES mocovi_tipo_dependencia(id_tipo_dependencia);


--
-- TOC entry 2348 (class 2606 OID 34819)
-- Name: fk_vinculo_designacion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY vinculo
    ADD CONSTRAINT fk_vinculo_designacion FOREIGN KEY (cargo) REFERENCES designacion(id_designacion);


--
-- TOC entry 2349 (class 2606 OID 34824)
-- Name: fk_vinculo_designacion_vinc; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY vinculo
    ADD CONSTRAINT fk_vinculo_designacion_vinc FOREIGN KEY (vinc) REFERENCES designacion(id_designacion);


--
-- TOC entry 2366 (class 0 OID 0)
-- Dependencies: 5
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2015-09-08 07:33:37

--
-- PostgreSQL database dump complete
--

