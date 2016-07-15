DROP TABLE IF EXISTS test_during_downtimes;
CREATE TABLE test_during_downtimes (
  timestamp int(11) NOT NULL DEFAULT '0',
  event_type int(11) NOT NULL DEFAULT '0',
  flags int(11) DEFAULT NULL,
  attrib int(11) DEFAULT NULL,
  host_name varchar(160) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT '',
  service_description varchar(160) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT '',
  state int(2) NOT NULL DEFAULT '0',
  hard int(2) NOT NULL DEFAULT '0',
  retry int(5) NOT NULL DEFAULT '0',
  downtime_depth int(11) DEFAULT NULL,
  output text CHARACTER SET latin1 COLLATE latin1_general_cs
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO test_during_downtimes VALUES
	(1202684400,100,NULL,NULL,'','',0,0,0,NULL,NULL),
	(1202684400,801,NULL,NULL, 'host1','',1,1,3,0,NULL),
	(1202688000,1103,NULL,NULL,'host1','',0,0,0,1,NULL),
	(1202694000,1103,NULL,NULL,'host1','',0,0,0,1,NULL),
	(1202695200,1104,NULL,NULL,'host1','',0,0,0,0,NULL),
	(1202695800,1104,NULL,NULL,'host1','',0,0,0,0,NULL);
