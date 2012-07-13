ALTER TABLE ninja_widgets ADD COLUMN instance_id INT DEFAULT NULL;
ALTER TABLE ninja_widgets MODIFY username varchar(200) DEFAULT NULL;
ALTER TABLE ninja_widgets MODIFY page varchar(200) NOT NULL DEFAULT 'tac/index';
UPDATE ninja_widgets SET username=NULL WHERE username='' OR username=' ';
UPDATE ninja_widgets SET instance_id=1 WHERE username IS NOT NULL;
-- this is highly insane, but it just might work, right?
UPDATE ninja_settings SET setting=REPLACE(REPLACE(setting, '|', '-1|'), ',', '-1,') WHERE type='widget_order';
