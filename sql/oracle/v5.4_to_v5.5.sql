CREATE TABLE ninja_saved_searches (
 id NUMBER(10,0) NOT NULL,
 username VARCHAR2(255 CHAR) DEFAULT NULL,
 search_name VARCHAR2(255 CHAR) NOT NULL,
 search_query VARCHAR2(255 CHAR) NOT NULL,
 search_description VARCHAR2(255 CHAR) NOT NULL
);

ALTER TABLE ninja_saved_searches
ADD CONSTRAINT ninja_saved_searches_pk PRIMARY KEY(id) ENABLE;

CREATE INDEX n_s_s_username ON ninja_saved_searches(username);

ALTER TABLE scheduled_reports ADD local_persistent_filepath VARCHAR2(200 CHAR) DEFAULT NULL;

UPDATE ninja_db_version SET version=2;
UPDATE sla_db_version SET version=8;
UPDATE avail_db_version SET version=8;
UPDATE scheduled_reports_db_version SET version=8;

/

CREATE OR REPLACE TRIGGER saved_searches_id_TRG BEFORE INSERT OR UPDATE ON ninja_saved_searches
FOR EACH ROW
DECLARE
v_newVal NUMBER(12) := 0;
v_incval NUMBER(12) := 0;
BEGIN
  IF INSERTING AND :new.id IS NULL THEN
    SELECT saved_searches_id_SEQ.NEXTVAL INTO v_newVal FROM DUAL;
    -- If this is the first time this table have been inserted into (sequence == 1)
    IF v_newVal = 1 THEN
      --get the max indentity value from the table
      SELECT NVL(max(id),0) INTO v_newVal FROM ninja_saved_searches;
      v_newVal := v_newVal + 1;
      --set the sequence to that value
      LOOP
           EXIT WHEN v_incval>=v_newVal;
           SELECT saved_searches_id_SEQ.nextval INTO v_incval FROM dual;
      END LOOP;
    END IF;
    --used to emulate LAST_INSERT_ID()
    --mysql_utilities.identity := v_newVal;
   -- assign the value from the sequence to emulate the identity column
   :new.id := v_newVal;
  END IF;
END;

/
