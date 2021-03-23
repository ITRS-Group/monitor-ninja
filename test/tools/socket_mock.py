#!/usr/bin/env python3
import argparse
import socket
import time


def endless_sleep():
    print("Going into endless sleep")
    while True:
        time.sleep(100)


def start_server(args):
    print("Setting up and listening to socket @ %s" % args.socket_path)
    s = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
    s.bind(args.socket_path)
    s.listen(1)

    if args.stop_before_accept:
        print("Stopping before accept")
        endless_sleep()

    while True:
        print("Accepting connection")
        conn, _ = s.accept()
        print("Reading from socket")
        indata = conn.recv(512)
        if args.no_answer:
            print("Will not answer")
            endless_sleep()
        print("Sending '%s' to socket" % args.custom_answer)
        conn.sendall(args.custom_answer)
        print("Closing socket")
        conn.close()


def parse_args():
    p = argparse.ArgumentParser()
    p.add_argument("--stop-before-accept", action="store_true", help="daemon doesn't accept socket connections")
    p.add_argument("--no-answer", action="store_true", help="daemon doesn't answer on socket")
    p.add_argument("--custom-answer", metavar="ANSWER", default="PONG", help="daemon replies with custom message")
    p.add_argument("socket_path", help="Path to unix socket")
    args = p.parse_args()
    return args


if __name__ == "__main__":
    options = parse_args()
    start_server(options)
