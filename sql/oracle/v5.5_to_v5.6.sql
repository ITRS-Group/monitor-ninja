ALTER TABLE ninja_widgets ADD instance_id NUMBER(10,0) DEFAULT NULL;
ALTER TABLE ninja_widgets MODIFY username VARCHAR2(200 CHAR) DEFAULT NULL;
ALTER TABLE ninja_widgets MODIFY page VARCHAR2(200 CHAR) DEFAULT 'tac/index';
UPDATE ninja_widgets SET username=NULL WHERE username='' OR username=' ';
UPDATE ninja_widgets SET instance_id=1 WHERE username IS NOT NULL;
UPDATE ninja_settings SET setting=REPLACE(REPLACE(setting, '|', '-1|'), ',', '-1,') WHERE type='widget_order';
UPDATE ninja_db_version SET version=5;

ALTER TABLE avail_config ADD include_trends NUMBER(1) DEFAULT 1;
UPDATE avail_db_version SET version=9;

INSERT INTO ninja_widgets ( page, name, friendly_name, setting) VALUES ('tac/index', 'nagvis', 'Nagvis', '');
COMMIT;
