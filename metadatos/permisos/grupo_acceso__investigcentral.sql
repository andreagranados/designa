
------------------------------------------------------------
-- apex_usuario_grupo_acc
------------------------------------------------------------
INSERT INTO apex_usuario_grupo_acc (proyecto, usuario_grupo_acc, nombre, nivel_acceso, descripcion, vencimiento, dias, hora_entrada, hora_salida, listar, permite_edicion, menu_usuario) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	'InvestigacionCentral', --nombre
	NULL, --nivel_acceso
	'Secretaría de Investigación para Central', --descripcion
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
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'1'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'2'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3635'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3636'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3687'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3730'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3731'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3732'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3735'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3736'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3737'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3738'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3739'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3743'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3757'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	NULL, --item_id
	'3765'  --item
);
--- FIN Grupo de desarrollo 0

------------------------------------------------------------
-- apex_grupo_acc_restriccion_funcional
------------------------------------------------------------
INSERT INTO apex_grupo_acc_restriccion_funcional (proyecto, usuario_grupo_acc, restriccion_funcional) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	'4'  --restriccion_funcional
);
INSERT INTO apex_grupo_acc_restriccion_funcional (proyecto, usuario_grupo_acc, restriccion_funcional) VALUES (
	'designa', --proyecto
	'investigcentral', --usuario_grupo_acc
	'17'  --restriccion_funcional
);
