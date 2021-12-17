#!/usr/bin/python3
import argparse
import signal
import subprocess
import threading


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--interval", type=int, default=60)
    args = parser.parse_args()
    _run(args.interval)


def _run(interval):
    event_exit = threading.Event()

    def _handler(*_):
        event_exit.set()  # unblocks wait()

    signal.signal(signal.SIGTERM, _handler)
    signal.signal(signal.SIGINT, _handler)

    while not event_exit.is_set():
        # Run the subprocess and let it fail loudly on non-zero exit code.
        # Systemd will take care of restarting it.
        subprocess.run(
            [
                "/usr/bin/php",
                "/opt/monitor/op5/ninja/index.php",
                "default/cron/schedule",
            ],
            check=True,
        )
        event_exit.wait(interval)


if __name__ == "__main__":
    main()
