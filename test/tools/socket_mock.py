#!/usr/bin/env python

import socket
import optparse
import time


def endless_sleep():
    print "Going into endless sleep"
    while True:
        time.sleep(100)


def start_server(socket_path, options):
    assert isinstance(socket_path, str)

    print "Setting up and listening to socket @ %s" % socket_path
    s = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
    s.bind(socket_path)
    s.listen(1)

    if options.stop_before_accept:
        print "Stopping before accept"
        endless_sleep()

    while True:
        print "Accepting connection"
        conn, _ = s.accept()
        print "Reading from socket"
        indata = conn.recv(512)
        if options.no_answer:
            print "Will not answer"
            endless_sleep()
        print "Sending '%s' to socket" % options.custom_answer
        conn.sendall(options.custom_answer)
        print "Closing socket"
        conn.close()


def parse_args():
    p = optparse.OptionParser(usage="usage: %prog [options] PATH_TO_SOCKET")
    p.add_option("--stop-before-accept", action="store_true", default=False, help="daemon doesn't accept socket connections")
    p.add_option("--no-answer", action="store_true", default=False, help="daemon doesn't answer on socket")
    p.add_option("--custom-answer", metavar="ANSWER", default="PONG", help="daemon replies with custom message")
    options, args = p.parse_args()
    if len(args) != 1:
        p.error("incorrect number of arguments")
    return options, args[0]

if __name__ == '__main__':
    options, socket_path = parse_args()
    start_server(socket_path, options)
