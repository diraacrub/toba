
------------------------------------------------------------
-- apex_usuario_grupo_acc
------------------------------------------------------------
INSERT INTO apex_usuario_grupo_acc (proyecto, usuario_grupo_acc, nombre, nivel_acceso, descripcion, vencimiento, dias, hora_entrada, hora_salida, listar, permite_edicion, menu_usuario) VALUES (
	'catedras', --proyecto
	'saccrub', --usuario_grupo_acc
	'Secretar�a Acad�mica CRUB', --nombre
	NULL, --nivel_acceso
	NULL, --descripcion
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
	'catedras', --proyecto
	'saccrub', --usuario_grupo_acc
	NULL, --item_id
	'1'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'catedras', --proyecto
	'saccrub', --usuario_grupo_acc
	NULL, --item_id
	'2'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'catedras', --proyecto
	'saccrub', --usuario_grupo_acc
	NULL, --item_id
	'3477'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'catedras', --proyecto
	'saccrub', --usuario_grupo_acc
	NULL, --item_id
	'3478'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'catedras', --proyecto
	'saccrub', --usuario_grupo_acc
	NULL, --item_id
	'3485'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'catedras', --proyecto
	'saccrub', --usuario_grupo_acc
	NULL, --item_id
	'3493'  --item
);
INSERT INTO apex_usuario_grupo_acc_item (proyecto, usuario_grupo_acc, item_id, item) VALUES (
	'catedras', --proyecto
	'saccrub', --usuario_grupo_acc
	NULL, --item_id
	'3499'  --item
);
--- FIN Grupo de desarrollo 0
