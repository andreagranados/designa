
------------------------------------------------------------
-- apex_usuario_grupo_acc
------------------------------------------------------------
INSERT INTO apex_usuario_grupo_acc (proyecto, usuario_grupo_acc, nombre, nivel_acceso, descripcion, vencimiento, dias, hora_entrada, hora_salida, listar, permite_edicion, menu_usuario) VALUES (
	'designa', --proyecto
	'actualiza_disciplinas', --usuario_grupo_acc
	'Actualiza_Disciplinas_Mincyt', --nombre
	NULL, --nivel_acceso
	'Actualiza las disciplinas Mincyt', --descripcion
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
	'actualiza_disciplinas', --usuario_grupo_acc
	NULL, --item_id
	'1'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'actualiza_disciplinas', --usuario_grupo_acc
	NULL, --item_id
	'3635'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'actualiza_disciplinas', --usuario_grupo_acc
	NULL, --item_id
	'3731'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'designa', --proyecto
	'actualiza_disciplinas', --usuario_grupo_acc
	NULL, --item_id
	'3848'  --item
);
--- FIN Grupo de desarrollo 0
