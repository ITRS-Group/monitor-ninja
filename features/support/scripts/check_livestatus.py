import sys
from livestatus import SingleSiteConnection

'''
Helper script to "independently" check the status of a livestatus unix socket.
'''

def check_livestatus(sockpath):
	connection = SingleSiteConnection(sockpath)
	connection.connect()
	assert len(connection.query("GET status")) > 0
	connection.disconnect()

if __name__ == '__main__':
	check_livestatus(sys.argv[1])
	#if we didn't crash yet, success!
	print "Livestatus OK"
