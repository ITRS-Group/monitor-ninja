CC = gcc
CFLAGS = -Wall -O2 -ggdb3 -pipe
SRC_C = $(wildcard *.c)
OBJS = $(patsubst %.c,%.o,$(SRC_C))
PROGS = apr_md5_validate

$(PROGS):

clean:
	rm -f $(PROGS) $(OBJS) core core.*

.PHONY:	clean
