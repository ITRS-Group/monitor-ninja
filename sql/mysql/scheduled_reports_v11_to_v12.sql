UPDATE scheduled_reports SET report_time = '00:00', report_on = '{"day_no":1}', report_period = '{"no":1,"period_id":"3"}' WHERE period_id = 3 AND report_on IS NULL;

UPDATE scheduled_reports SET report_time = '00:00', report_on = '[{"day":1}]', report_period = '{"no":1,"period_id":"1"}' WHERE period_id = 1 AND report_on IS NULL;

UPDATE scheduled_reports SET report_time = '00:00', report_on = '{"day_no":"1","day":"1"}', report_period = '{"no":1,"period_id":"2"}' WHERE period_id = 2 AND report_on IS NULL;