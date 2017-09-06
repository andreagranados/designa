
------------------------------------------------------------
-- apex_usuario_grupo_acc
------------------------------------------------------------
INSERT INTO apex_usuario_grupo_acc (proyecto, usuario_grupo_acc, nombre, nivel_acceso, descripcion, vencimiento, dias, hora_entrada, hora_salida, listar, permite_edicion, menu_usuario) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	'Extension', --nombre
	NULL, --nivel_acceso
	'Secretaría de Extensión', --descripcion
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
	'extension', --usuario_grupo_acc
	NULL, --item_id
	'1'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	NULL, --item_id
	'2'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	NULL, --item_id
	'3635'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	NULL, --item_id
	'3636'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	NULL, --item_id
	'3652'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	NULL, --item_id
	'3687'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	NULL, --item_id
	'3692'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	NULL, --item_id
	'3697'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	NULL, --item_id
	'3725'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	NULL, --item_id
	'3726'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	NULL, --item_id
	'3728'  --item
);
--- FIN Grupo de desarrollo 0

------------------------------------------------------------
-- apex_grupo_acc_restriccion_funcional
------------------------------------------------------------
INSERT INTO apex_grupo_acc_restriccion_funcional (proyecto, usuario_grupo_acc, restriccion_funcional) VALUES (
	'designa', --proyecto
	'extension', --usuario_grupo_acc
	'3'  --restriccion_funcional
);
