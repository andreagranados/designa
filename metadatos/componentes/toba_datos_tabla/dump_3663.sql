------------------------------------------------------------
--[3663]--  DT - logs_designacion 
------------------------------------------------------------

------------------------------------------------------------
-- apex_objeto
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_objeto (proyecto, objeto, anterior, identificador, reflexivo, clase_proyecto, clase, punto_montaje, subclase, subclase_archivo, objeto_categoria_proyecto, objeto_categoria, nombre, titulo, colapsable, descripcion, fuente_datos_proyecto, fuente_datos, solicitud_registrar, solicitud_obj_obs_tipo, solicitud_obj_observacion, parametro_a, parametro_b, parametro_c, parametro_d, parametro_e, parametro_f, usuario, creacion, posicion_botonera) VALUES (
	'designa', --proyecto
	'3663', --objeto
	NULL, --anterior
	NULL, --identificador
	NULL, --reflexivo
	'toba', --clase_proyecto
	'toba_datos_tabla', --clase
	'23', --punto_montaje
	'dt_logs_designacion', --subclase
	'datos/dt_logs_designacion.php', --subclase_archivo
	NULL, --objeto_categoria_proyecto
	NULL, --objeto_categoria
	'DT - logs_designacion', --nombre
	NULL, --titulo
	NULL, --colapsable
	NULL, --descripcion
	'designa', --fuente_datos_proyecto
	'designa', --fuente_datos
	NULL, --solicitud_registrar
	NULL, --solicitud_obj_obs_tipo
	NULL, --solicitud_obj_observacion
	NULL, --parametro_a
	NULL, --parametro_b
	NULL, --parametro_c
	NULL, --parametro_d
	NULL, --parametro_e
	NULL, --parametro_f
	NULL, --usuario
	'2016-03-10 18:37:20', --creacion
	NULL  --posicion_botonera
);
--- FIN Grupo de desarrollo 0

------------------------------------------------------------
-- apex_objeto_db_registros
------------------------------------------------------------
INSERT INTO apex_objeto_db_registros (objeto_proyecto, objeto, max_registros, min_registros, punto_montaje, ap, ap_clase, ap_archivo, tabla, tabla_ext, alias, modificar_claves, fuente_datos_proyecto, fuente_datos, permite_actualizacion_automatica, esquema, esquema_ext) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	NULL, --max_registros
	NULL, --min_registros
	'23', --punto_montaje
	'1', --ap
	NULL, --ap_clase
	NULL, --ap_archivo
	'logs_designacion', --tabla
	NULL, --tabla_ext
	NULL, --alias
	'0', --modificar_claves
	'designa', --fuente_datos_proyecto
	'designa', --fuente_datos
	'1', --permite_actualizacion_automatica
	'public_auditoria', --esquema
	'public'  --esquema_ext
);

------------------------------------------------------------
-- apex_objeto_db_registros_col
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1471', --col_id
	'auditoria_usuario', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'60', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1472', --col_id
	'auditoria_fecha', --columna
	'T', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1473', --col_id
	'auditoria_operacion', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'1', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1474', --col_id
	'auditoria_id_solicitud', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1475', --col_id
	'id_designacion', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1476', --col_id
	'id_docente', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1477', --col_id
	'nro_cargo', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1478', --col_id
	'anio_acad', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1479', --col_id
	'desde', --columna
	'F', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1480', --col_id
	'hasta', --columna
	'F', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1481', --col_id
	'cat_mapuche', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'4', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1482', --col_id
	'cat_estat', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'6', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1483', --col_id
	'dedic', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1484', --col_id
	'carac', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'1', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1485', --col_id
	'uni_acad', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'5', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1486', --col_id
	'id_departamento', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1487', --col_id
	'id_area', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1488', --col_id
	'id_orientacion', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1489', --col_id
	'id_norma', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1490', --col_id
	'id_expediente', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1491', --col_id
	'tipo_incentivo', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1492', --col_id
	'dedi_incen', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1493', --col_id
	'cic_con', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'3', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1494', --col_id
	'cargo_gestion', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'4', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1495', --col_id
	'ord_gestion', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'10', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1496', --col_id
	'emite_cargo_gestion', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'4', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1497', --col_id
	'nro_gestion', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'10', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1498', --col_id
	'observaciones', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1499', --col_id
	'check_presup', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1500', --col_id
	'nro_540', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1501', --col_id
	'concursado', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1502', --col_id
	'check_academica', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1503', --col_id
	'tipo_desig', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1504', --col_id
	'id_reserva', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1505', --col_id
	'estado', --columna
	'C', --tipo
	'1', --pk
	'', --secuencia
	'1', --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1506', --col_id
	'id_norma_cs', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
INSERT INTO apex_objeto_db_registros_col (objeto_proyecto, objeto, col_id, columna, tipo, pk, secuencia, largo, no_nulo, no_nulo_db, externa, tabla) VALUES (
	'designa', --objeto_proyecto
	'3663', --objeto
	'1507', --col_id
	'por_permuta', --columna
	'E', --tipo
	'1', --pk
	'', --secuencia
	NULL, --largo
	NULL, --no_nulo
	'0', --no_nulo_db
	'0', --externa
	'logs_designacion'  --tabla
);
--- FIN Grupo de desarrollo 0
