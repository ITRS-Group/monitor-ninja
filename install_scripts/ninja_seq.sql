SPOOL ninja_seq.out
SET DEFINE OFF;
SET SCAN OFF;
-- PROMPT Creating User ninja ...
-- CREATE USER ninja IDENTIFIED BY ninja DEFAULT TABLESPACE USERS TEMPORARY TABLESPACE TEMP;
-- GRANT CREATE SESSION, RESOURCE, CREATE VIEW, CREATE MATERIALIZED VIEW, CREATE SYNONYM TO ninja;
-- connect ninja/ninja;
connect merlin/merlin;

-- DROP SEQUENCE ninja_widgets_id_SEQ;


PROMPT Creating Sequence ninja_widgets_id_SEQ ...
CREATE SEQUENCE  ninja_widgets_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE recurring_downtime_id_SEQ;


PROMPT Creating Sequence recurring_downtime_id_SEQ ...
CREATE SEQUENCE  recurring_downtime_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE scheduled_report_types_id_SEQ;


PROMPT Creating Sequence scheduled_report_types_id_SEQ ...
CREATE SEQUENCE  scheduled_report_types_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE avail_config_objects_id_SEQ;


PROMPT Creating Sequence avail_config_objects_id_SEQ ...
CREATE SEQUENCE  avail_config_objects_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE roles_id_SEQ;


PROMPT Creating Sequence roles_id_SEQ ...
CREATE SEQUENCE  roles_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE ninja_user_authorization_id_SE;


PROMPT Creating Sequence ninja_user_authorization_id_SE ...
CREATE SEQUENCE  ninja_user_authorization_id_SE
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE scheduled_report_periods_id_SE;


PROMPT Creating Sequence scheduled_report_periods_id_SE ...
CREATE SEQUENCE  scheduled_report_periods_id_SE
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE users_id_SEQ;


PROMPT Creating Sequence users_id_SEQ ...
CREATE SEQUENCE  users_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE sla_periods_id_SEQ;


PROMPT Creating Sequence sla_periods_id_SEQ ...
CREATE SEQUENCE  sla_periods_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE sla_config_id_SEQ;


PROMPT Creating Sequence sla_config_id_SEQ ...
CREATE SEQUENCE  sla_config_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE ninja_settings_id_SEQ;


PROMPT Creating Sequence ninja_settings_id_SEQ ...
CREATE SEQUENCE  ninja_settings_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE scheduled_reports_id_SEQ;


PROMPT Creating Sequence scheduled_reports_id_SEQ ...
CREATE SEQUENCE  scheduled_reports_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE sla_config_objects_id_SEQ;


PROMPT Creating Sequence sla_config_objects_id_SEQ ...
CREATE SEQUENCE  sla_config_objects_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE user_tokens_id_SEQ;


PROMPT Creating Sequence user_tokens_id_SEQ ...
CREATE SEQUENCE  user_tokens_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE avail_config_id_SEQ;


PROMPT Creating Sequence avail_config_id_SEQ ...
CREATE SEQUENCE  avail_config_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP SEQUENCE ninja_db_version_id_SEQ;


PROMPT Creating Sequence ninja_db_version_id_SEQ ...
CREATE SEQUENCE  ninja_db_version_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

PROMPT Creating Sequence summary_config_id_SEQ ...
CREATE SEQUENCE  summary_config_id_SEQ
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

-- DROP TABLE avail_config CASCADE CONSTRAINTS;


PROMPT Creating Table avail_config ...
CREATE TABLE avail_config (
  id NUMBER(10,0) NOT NULL,
  username VARCHAR2(255 CHAR) DEFAULT NULL,
  report_name VARCHAR2(255 CHAR) NOT NULL,
  info CLOB DEFAULT NULL,
  created DATE DEFAULT SYSDATE NOT NULL,
  rpttimeperiod VARCHAR2(75 CHAR) DEFAULT NULL,
  report_period VARCHAR2(50 CHAR) DEFAULT NULL,
  start_time NUMBER(10,0) DEFAULT '0' NOT NULL,
  end_time NUMBER(10,0) DEFAULT '0' NOT NULL,
  report_type VARCHAR2(30 CHAR) NOT NULL,
  initialassumedhoststate NUMBER(10,0) DEFAULT '0',
  initialassumedservicestate NUMBER(10,0) DEFAULT '0',
  assumeinitialstates NUMBER(10,0) DEFAULT '0' NOT NULL,
  scheduleddowntimeasuptime NUMBER(10,0) DEFAULT '0',
  assumestatesduringnotrunning NUMBER(10,0) DEFAULT '0',
  includesoftstates NUMBER(10,0) DEFAULT '0',
  updated DATE DEFAULT to_date('01-JAN-70 00:00:00', 'dd-MON-yy hh24:mi:ss') NOT NULL,
  use_average NUMBER(3,0) DEFAULT '0',
  use_alias NUMBER(3,0) DEFAULT '0',
  cluster_mode NUMBER(10,0) DEFAULT '0',
  host_filter_status VARCHAR(100 CHAR) DEFAULT NULL,
  service_filter_status VARCHAR(100 CHAR) DEFAULT NULL,
  use_summary NUMBER(3,0) DEFAULT '0',
  use_pnp NUMBER(3,0) DEFAULT '0',
  summary_report_type NUMBER(3,0) DEFAULT '0',
  summary_items NUMBER(3,0) DEFAULT '0',
  alert_types NUMBER(3,0) DEFAULT '0',
  state_types NUMBER(3,0) DEFAULT '0',
  host_states NUMBER(3,0) DEFAULT '0',
  service_states NUMBER(3,0) DEFAULT '0'
);


PROMPT Creating Primary Key Constraint PRIMARY_14 on table avail_config ...
ALTER TABLE avail_config
ADD CONSTRAINT PRIMARY_14 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Index user__1 on avail_config ...
CREATE INDEX user_1 ON avail_config
(
  username
)
;

-- DROP TABLE avail_config_objects CASCADE CONSTRAINTS;


PROMPT Creating Table avail_config_objects ...
CREATE TABLE avail_config_objects (
  id NUMBER(10,0) NOT NULL,
  avail_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(255 CHAR) NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_16 on table avail_config_objects ...
ALTER TABLE avail_config_objects
ADD CONSTRAINT PRIMARY_16 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Index avail_id on avail_config_objects ...
CREATE INDEX avail_id ON avail_config_objects
(
  avail_id
)
;

-- DROP TABLE avail_db_version CASCADE CONSTRAINTS;


PROMPT Creating Table avail_db_version ...
CREATE TABLE avail_db_version (
  version NUMBER(10,0) DEFAULT '0' NOT NULL
);



INSERT INTO avail_db_version VALUES(7);
commit;


-- DROP TABLE summary_config CASCADE CONSTRAINTS;

PROMPT Creating Table summary_config ...
CREATE TABLE summary_config (
  id NUMBER(11,0) NOT NULL,
  username varchar2(200 CHAR) NOT NULL,
  report_name varchar2(200 CHAR) NOT NULL,
  setting CLOB NOT NULL
);


PROMPT Creating Primary Key Constraint summary_config_pk on table summary_config ...
ALTER TABLE summary_config
ADD CONSTRAINT summary_config_pk PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Index username_sum_conf on summary_config ...
CREATE INDEX username_sum_conf ON summary_config
(
  username
)
;


-- DROP TABLE ninja_db_version CASCADE CONSTRAINTS;


PROMPT Creating Table ninja_db_version ...
CREATE TABLE ninja_db_version (
  id NUMBER(10,0) NOT NULL,
  version NUMBER(10,0) DEFAULT '0' NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_17 on table ninja_db_version ...
ALTER TABLE ninja_db_version
ADD CONSTRAINT PRIMARY_17 PRIMARY KEY
(
  id
)
ENABLE
;

--
-- Data for table `ninja_db_version`
--

INSERT INTO ninja_db_version (id, version) VALUES(1, 1);
commit;

-- DROP TABLE ninja_settings CASCADE CONSTRAINTS;


PROMPT Creating Table ninja_settings ...
CREATE TABLE ninja_settings (
  id NUMBER(10,0) NOT NULL,
  username VARCHAR2(200 CHAR) DEFAULT NULL,
  page VARCHAR2(200 CHAR) NOT NULL,
  type VARCHAR2(200 CHAR) NOT NULL,
  setting CLOB DEFAULT NULL,
  widget_id NUMBER(10,0)
);


PROMPT Creating Primary Key Constraint PRIMARY_12 on table ninja_settings ...
ALTER TABLE ninja_settings
ADD CONSTRAINT PRIMARY_12 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Index user_3 on ninja_settings ...
CREATE INDEX user_3 ON ninja_settings
(
  username
)
;
PROMPT Creating Index page on ninja_settings ...
CREATE INDEX page ON ninja_settings
(
  page
)
;
PROMPT Creating Index widget_id on ninja_settings ...
CREATE INDEX widget_id ON ninja_settings
(
  widget_id
)
;

INSERT INTO ninja_settings (id,username,page, type, setting)
 VALUES(1, '', 'tac/index', 'widget_order', 'widget-placeholder=widget-netw_outages,widget-tac_scheduled,widget-monitoring_performance|widget-placeholder1=widget-tac_disabled,widget-tac_acknowledged|widget-placeholder2=widget-netw_health,widget-geomap|widget-placeholder3=widget-tac_hosts,widget-tac_services,widget-tac_monfeat,widget-tac_problems');
commit;

-- DROP TABLE ninja_user_authorization CASCADE CONSTRAINTS;


PROMPT Creating Table ninja_user_authorization ...
CREATE TABLE ninja_user_authorization (
  id NUMBER(10,0) NOT NULL,
  user_id NUMBER(10,0) NOT NULL,
  system_information NUMBER(10,0) DEFAULT '0' NOT NULL,
  configuration_information NUMBER(10,0) DEFAULT '0' NOT NULL,
  system_commands NUMBER(10,0) DEFAULT '0' NOT NULL,
  all_services NUMBER(10,0) DEFAULT '0' NOT NULL,
  all_hosts NUMBER(10,0) DEFAULT '0' NOT NULL,
  all_service_commands NUMBER(10,0) DEFAULT '0' NOT NULL,
  all_host_commands NUMBER(10,0) DEFAULT '0' NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_9 on table ninja_user_authorization ...
ALTER TABLE ninja_user_authorization
ADD CONSTRAINT PRIMARY_9 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Index user_id on ninja_user_authorization ...
CREATE INDEX user_id ON ninja_user_authorization
(
  user_id
)
;

-- DROP TABLE ninja_widgets CASCADE CONSTRAINTS;


PROMPT Creating Table ninja_widgets ...
CREATE TABLE ninja_widgets (
  id NUMBER(10,0) NOT NULL,
  username VARCHAR2(200 CHAR) DEFAULT NULL,
  page VARCHAR2(200 CHAR) NOT NULL,
  name VARCHAR2(255 CHAR) NOT NULL,
  friendly_name VARCHAR2(255 CHAR) NOT NULL,
  setting CLOB DEFAULT NULL
);

PROMPT Creating Primary Key Constraint PRIMARY_1 on table ninja_widgets ...
ALTER TABLE ninja_widgets
ADD CONSTRAINT PRIMARY_1 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Index user on ninja_widgets ...
CREATE INDEX username ON ninja_widgets
(
  username
)
;

--
-- Data for table `ninja_widgets`
--

INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(1, '', 'tac/index', 'tac_problems', 'Unhandled problems', '');
INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(2, '', 'tac/index', 'netw_health', 'Network health', '');
INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(3, '', 'tac/index', 'tac_scheduled', 'Scheduled downtime', '');
INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(4, '', 'tac/index', 'tac_acknowledged', 'Acknowledged problems', '');
INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(5, '', 'tac/index', 'tac_disabled', 'Disabled checks', '');
INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(6, '', 'tac/index', 'netw_outages', 'Network outages', '');
INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(7, '', 'tac/index', 'tac_hosts', 'Hosts', '');
INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(8, '', 'tac/index', 'tac_services', 'Services', '');
INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(9, '', 'tac/index', 'tac_monfeat', 'Monitoring features', '');
INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(10, '', 'status', 'status_totals', 'Status Totals', '');
INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(11, '', 'tac/index', 'monitoring_performance', 'Monitoring Performance', '');
INSERT INTO ninja_widgets (id, username, page, name, friendly_name, setting) VALUES 	(12, '', 'tac/index', 'geomap', 'Geomap', '');
commit;

-- DROP TABLE recurring_downtime CASCADE CONSTRAINTS;


PROMPT Creating Table recurring_downtime ...
CREATE TABLE recurring_downtime (
  id NUMBER(10,0) NOT NULL,
  author VARCHAR2(255 CHAR) NOT NULL,
  downtime_type VARCHAR2(255 CHAR) NOT NULL,
  data CLOB NOT NULL,
  last_update NUMBER(10,0) DEFAULT '0' NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_2 on table recurring_downtime ...
ALTER TABLE recurring_downtime
ADD CONSTRAINT PRIMARY_2 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Index author on recurring_downtime ...
CREATE INDEX author ON recurring_downtime
(
  author
)
;
PROMPT Creating Index downtime_type on recurring_downtime ...
CREATE INDEX downtime_type ON recurring_downtime
(
  downtime_type
)
;

-- DROP TABLE roles CASCADE CONSTRAINTS;


PROMPT Creating Table roles ...
CREATE TABLE roles (
  id NUMBER(10,0) NOT NULL,
  name VARCHAR2(100 CHAR) NOT NULL,
  description VARCHAR2(255 CHAR) NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_13 on table roles ...
ALTER TABLE roles
ADD CONSTRAINT PRIMARY_13 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Unique Index uniq_name on roles...
CREATE UNIQUE INDEX uniq_name ON roles
(
  name
)
;

--
-- Data for table `roles`
--

INSERT INTO roles (id, name, description) VALUES (1, 'login', 'Login privileges, granted after account confirmation');
INSERT INTO roles (id, name, description) VALUES (2, 'admin', 'Administrative user, has access to everything.');
commit;

-- DROP TABLE roles_users CASCADE CONSTRAINTS;


PROMPT Creating Table roles_users ...
CREATE TABLE roles_users (
  user_id NUMBER(10,0) NOT NULL,
  role_id NUMBER(10,0) NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_3 on table roles_users ...
ALTER TABLE roles_users
ADD CONSTRAINT PRIMARY_3 PRIMARY KEY
(
  user_id,
  role_id
)
ENABLE
;
PROMPT Creating Index fk_role_id on roles_users ...
CREATE INDEX fk_role_id ON roles_users
(
  role_id
)
;

-- DROP TABLE scheduled_report_periods CASCADE CONSTRAINTS;


PROMPT Creating Table scheduled_report_periods ...
CREATE TABLE scheduled_report_periods (
  id NUMBER(10,0) NOT NULL,
  periodname VARCHAR2(100 CHAR) NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_5 on table scheduled_report_periods ...
ALTER TABLE scheduled_report_periods
ADD CONSTRAINT PRIMARY_5 PRIMARY KEY
(
  id
)
ENABLE
;

INSERT INTO scheduled_report_periods (id, periodname) VALUES (1, 'Weekly');
INSERT INTO scheduled_report_periods (id, periodname) VALUES (2, 'Monthly');
INSERT INTO scheduled_report_periods (id, periodname) VALUES (3, 'Daily');
commit;

-- DROP TABLE scheduled_report_types CASCADE CONSTRAINTS;


PROMPT Creating Table scheduled_report_types ...
CREATE TABLE scheduled_report_types (
  id NUMBER(10,0) NOT NULL,
  name VARCHAR2(255 CHAR) NOT NULL,
  script_reports_path VARCHAR2(255 CHAR) NOT NULL,
  script_reports_run VARCHAR2(255 CHAR) NOT NULL,
  identifier VARCHAR2(50 CHAR) NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_8 on table scheduled_report_types ...
ALTER TABLE scheduled_report_types
ADD CONSTRAINT PRIMARY_8 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Index identifier on scheduled_report_types ...
CREATE INDEX identifier ON scheduled_report_types
(
  identifier
)
;

INSERT INTO scheduled_report_types (id, name, script_reports_path, script_reports_run, identifier) VALUES (1, ' ', ' ', ' ', 'avail');
INSERT INTO scheduled_report_types (id, name, script_reports_path, script_reports_run, identifier) VALUES (2, ' ', ' ', ' ', 'sla');
INSERT INTO scheduled_report_types (id, name, script_reports_path, script_reports_run, identifier) VALUES (3, ' ', ' ', ' ', 'summary');
commit;

-- DROP TABLE scheduled_reports CASCADE CONSTRAINTS;


PROMPT Creating Table scheduled_reports ...
CREATE TABLE scheduled_reports (
  id NUMBER(10,0) NOT NULL,
  username VARCHAR2(255 CHAR) DEFAULT NULL,
  report_type_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  report_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  recipients CLOB NOT NULL,
  description CLOB NOT NULL,
  period_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  filename VARCHAR2(255 CHAR) NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_4 on table scheduled_reports ...
ALTER TABLE scheduled_reports
ADD CONSTRAINT PRIMARY_4 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Index report_type_id on scheduled_reports ...
CREATE INDEX report_type_id ON scheduled_reports
(
  report_type_id
)
;
PROMPT Creating Index user_2 on scheduled_reports ...
CREATE INDEX user_2 ON scheduled_reports
(
  username
)
;

-- DROP TABLE scheduled_reports_db_version CASCADE CONSTRAINTS;


PROMPT Creating Table scheduled_reports_db_version ...
CREATE TABLE scheduled_reports_db_version (
  version VARCHAR2(10 CHAR) NOT NULL
);



INSERT INTO scheduled_reports_db_version VALUES('1.0.0');
commit;

-- DROP TABLE sla_config CASCADE CONSTRAINTS;


PROMPT Creating Table sla_config ...
CREATE TABLE sla_config (
  id NUMBER(10,0) NOT NULL,
  username VARCHAR2(255 CHAR) DEFAULT NULL,
  sla_name VARCHAR2(255 CHAR) NOT NULL,
  info CLOB DEFAULT NULL,
  created DATE DEFAULT SYSDATE NOT NULL,
  rpttimeperiod VARCHAR2(75 CHAR) DEFAULT NULL,
  report_period VARCHAR2(50 CHAR) DEFAULT NULL,
  start_time NUMBER(10,0) DEFAULT '0' NOT NULL,
  end_time NUMBER(10,0) DEFAULT '0' NOT NULL,
  report_type VARCHAR2(30 CHAR) NOT NULL,
  initialassumedhoststate NUMBER(10,0) DEFAULT '0' NOT NULL,
  initialassumedservicestate NUMBER(10,0) DEFAULT '0' NOT NULL,
  assumeinitialstates NUMBER(10,0) DEFAULT '0' NOT NULL,
  scheduleddowntimeasuptime NUMBER(10,0) DEFAULT '0',
  assumestatesduringnotrunning NUMBER(10,0) DEFAULT '0',
  includesoftstates NUMBER(10,0) DEFAULT '0',
  updated DATE DEFAULT to_date('01-JAN-70 00:00:00', 'dd-MON-yy hh24:mi:ss') NOT NULL,
  use_average NUMBER(3,0) DEFAULT '0',
  use_alias NUMBER(3,0) DEFAULT '0',
  cluster_mode NUMBER(10,0) DEFAULT '0',
  use_summary NUMBER(3,0) DEFAULT '0',
  use_pnp NUMBER(3,0) DEFAULT '0',
  summary_report_type NUMBER(3,0) DEFAULT '0',
  summary_items NUMBER(3,0) DEFAULT '0',
  alert_types NUMBER(3,0) DEFAULT '0',
  state_types NUMBER(3,0) DEFAULT '0',
  host_states NUMBER(3,0) DEFAULT '0',
  service_states NUMBER(3,0) DEFAULT '0'
);


PROMPT Creating Primary Key Constraint PRIMARY_15 on table sla_config ...
ALTER TABLE sla_config
ADD CONSTRAINT PRIMARY_15 PRIMARY KEY
(
  id
)
ENABLE
;

-- DROP TABLE sla_config_objects CASCADE CONSTRAINTS;


PROMPT Creating Table sla_config_objects ...
CREATE TABLE sla_config_objects (
  id NUMBER(10,0) NOT NULL,
  sla_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(255 CHAR) NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_10 on table sla_config_objects ...
ALTER TABLE sla_config_objects
ADD CONSTRAINT PRIMARY_10 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Index sla_id on sla_config_objects ...
CREATE INDEX sla_id ON sla_config_objects
(
  sla_id
)
;

-- DROP TABLE sla_db_version CASCADE CONSTRAINTS;


PROMPT Creating Table sla_db_version ...
CREATE TABLE sla_db_version (
  version NUMBER(10,0) DEFAULT '0' NOT NULL
);



INSERT INTO sla_db_version VALUES(7);
commit;

-- DROP TABLE sla_periods CASCADE CONSTRAINTS;


PROMPT Creating Table sla_periods ...
CREATE TABLE sla_periods (
  id NUMBER(10,0) NOT NULL,
  sla_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(20 CHAR) NOT NULL,
  value FLOAT DEFAULT '0' NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_7 on table sla_periods ...
ALTER TABLE sla_periods
ADD CONSTRAINT PRIMARY_7 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Index sla_id_1 on sla_periods ...
CREATE INDEX sla_id_1 ON sla_periods
(
  sla_id
)
;

-- DROP TABLE user_tokens CASCADE CONSTRAINTS;


PROMPT Creating Table user_tokens ...
CREATE TABLE user_tokens (
  id NUMBER(10,0) NOT NULL,
  user_id NUMBER(10,0) NOT NULL,
  user_agent VARCHAR2(40 CHAR) NOT NULL,
  token VARCHAR2(32 CHAR) NOT NULL,
  created NUMBER(10,0) NOT NULL,
  expires NUMBER(10,0) NOT NULL
);


PROMPT Creating Primary Key Constraint PRIMARY_6 on table user_tokens ...
ALTER TABLE user_tokens
ADD CONSTRAINT PRIMARY_6 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Unique Index uniq_token on user_tokens...
CREATE UNIQUE INDEX uniq_token ON user_tokens
(
  token
)
;
PROMPT Creating Index fk_user_id on user_tokens ...
CREATE INDEX fk_user_id ON user_tokens
(
  user_id
)
;

-- DROP TABLE users CASCADE CONSTRAINTS;


PROMPT Creating Table users ...
CREATE TABLE users (
  id NUMBER(10,0) NOT NULL,
  realname VARCHAR2(100 CHAR) DEFAULT NULL, -- originally: NOT NULL
  email VARCHAR2(127 CHAR) DEFAULT NULL,
  username VARCHAR2(100 CHAR) NOT NULL,
  password_algo VARCHAR2(20 CHAR) DEFAULT 'b64_sha1' NOT NULL,
  password VARCHAR2(50 CHAR) NOT NULL,
  logins NUMBER(10,0) DEFAULT '0' NOT NULL,
  last_login NUMBER(10,0)
);


PROMPT Creating Primary Key Constraint PRIMARY_11 on table users ...
ALTER TABLE users
ADD CONSTRAINT PRIMARY_11 PRIMARY KEY
(
  id
)
ENABLE
;
PROMPT Creating Unique Index uniq_username on users...
CREATE UNIQUE INDEX uniq_username ON users
(
  username
)
;

connect merlin/merlin;

CREATE OR REPLACE TRIGGER ninja_widgets_id_TRG BEFORE INSERT OR UPDATE ON ninja_widgets
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  ninja_widgets_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM ninja_widgets;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT ninja_widgets_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER recurring_downtime_id_TRG BEFORE INSERT OR UPDATE ON recurring_downtime
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  recurring_downtime_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM recurring_downtime;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT recurring_downtime_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER scheduled_report_types_id_TRG BEFORE INSERT OR UPDATE ON scheduled_report_types
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  scheduled_report_types_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM scheduled_report_types;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT scheduled_report_types_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER avail_config_objects_id_TRG BEFORE INSERT OR UPDATE ON avail_config_objects
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  avail_config_objects_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM avail_config_objects;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT avail_config_objects_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER roles_id_TRG BEFORE INSERT OR UPDATE ON roles
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  roles_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM roles;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT roles_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER ninja_user_authorization_id_TR BEFORE INSERT OR UPDATE ON ninja_user_authorization
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  ninja_user_authorization_id_SE.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM ninja_user_authorization;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT ninja_user_authorization_id_SE.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER scheduled_report_periods_id_TR BEFORE INSERT OR UPDATE ON scheduled_report_periods
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  scheduled_report_periods_id_SE.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM scheduled_report_periods;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT scheduled_report_periods_id_SE.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER users_id_TRG BEFORE INSERT OR UPDATE ON users
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  users_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM users;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT users_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER sla_periods_id_TRG BEFORE INSERT OR UPDATE ON sla_periods
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  sla_periods_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM sla_periods;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT sla_periods_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER sla_config_id_TRG BEFORE INSERT OR UPDATE ON sla_config
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  sla_config_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM sla_config;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT sla_config_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER ninja_settings_id_TRG BEFORE INSERT OR UPDATE ON ninja_settings
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  ninja_settings_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM ninja_settings;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT ninja_settings_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER scheduled_reports_id_TRG BEFORE INSERT OR UPDATE ON scheduled_reports
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  scheduled_reports_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM scheduled_reports;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT scheduled_reports_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER sla_config_objects_id_TRG BEFORE INSERT OR UPDATE ON sla_config_objects
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  sla_config_objects_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM sla_config_objects;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT sla_config_objects_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER user_tokens_id_TRG BEFORE INSERT OR UPDATE ON user_tokens
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  user_tokens_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM user_tokens;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT user_tokens_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER avail_config_id_TRG BEFORE INSERT OR UPDATE ON avail_config
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  avail_config_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM avail_config;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT avail_config_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER ninja_db_version_id_TRG BEFORE INSERT OR UPDATE ON ninja_db_version
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  ninja_db_version_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM ninja_db_version;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT ninja_db_version_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

CREATE OR REPLACE TRIGGER summary_config_id_TRG BEFORE INSERT OR UPDATE ON summary_config
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT  summary_config_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM summary_config;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT summary_config_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/

-- DISCONNECT;
SPOOL OFF;
-- exit;

