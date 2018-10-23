
------------------------------------------------------------
-- apex_usuario_grupo_acc
------------------------------------------------------------
INSERT INTO apex_usuario_grupo_acc (proyecto, usuario_grupo_acc, nombre, nivel_acceso, descripcion, vencimiento, dias, hora_entrada, hora_salida, listar, permite_edicion, menu_usuario) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	'Academica_Consulta', --nombre
	NULL, --nivel_acceso
	'Ver listados académicos de solo consulta', --descripcion
	NULL, --vencimiento
	NULL, --dias
	NULL, --hora_entrada
	NULL, --hora_salida
	NULL, --listar
	'0', --permite_edicion
	NULL  --menu_usuario
);

------------------------------------------------------------
-- apex_usuario_grupo_acc_item
------------------------------------------------------------

--- INICIO Grupo de desarrollo 0
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'1'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'2'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3635'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3636'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3662'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3676'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3684'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3685'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3687'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3689'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3702'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3708'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3717'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3754'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3781'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3790'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	NULL, --item_id
	'3791'  --item
);
--- FIN Grupo de desarrollo 0

------------------------------------------------------------
-- apex_grupo_acc_restriccion_funcional
------------------------------------------------------------
INSERT INTO apex_grupo_acc_restriccion_funcional (proyecto, usuario_grupo_acc, restriccion_funcional) VALUES (
	'designa', --proyecto
	'academica_consulta', --usuario_grupo_acc
	'3'  --restriccion_funcional
);
