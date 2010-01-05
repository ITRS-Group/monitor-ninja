description = Test SLA with average and group availability for two hosts
logfile = average.log

global_vars {
	start_time = 1199200000
	end_time   = 1199230000
	host_name {
		testhost
		testhost2
	}
}

test 1: using SLA - Group availability {
	use_average = 0

	correct {
		TOTAL_TIME_UP   = 20000
		TOTAL_TIME_DOWN = 10000
	}
}

test 2: using average {
	use_average = 1

	correct {
		TOTAL_TIME_UP   = 25000
		TOTAL_TIME_DOWN = 5000
	}
}


# hela testperiodens klocktid: 30000s (2st = 60000s sammanslagen tid)
# 10000 sekunder är båda hostarna uppe (avg 100% up)
# 10000 sekunder ena hosten nere, 10000 sekunder uppe på andra hosten (avg 50% up)
# 10000 sekunder till är båda hostarna uppe (avg 100% up)
#
# = 40000 sekunder uppe, 20000 nere / 2 st = 20000 UP 10000 DOWN
# SLA: 4/6 ~= 0.666667
#
# = 50000 sekunder uppe, 10000 nere / 2 st = 25000 UP 5000 DOWN
# AVG: 5/6 ~= 0.833333
