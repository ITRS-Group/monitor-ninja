ALTER TABLE ninja_widgets ADD COLUMN instance_id INT DEFAULT NULL;
ALTER TABLE ninja_widgets MODIFY username varchar(200) DEFAULT NULL;
ALTER TABLE ninja_widgets MODIFY page varchar(200) NOT NULL DEFAULT 'tac/index';
UPDATE ninja_widgets SET username=NULL WHERE username='' OR username=' ';
