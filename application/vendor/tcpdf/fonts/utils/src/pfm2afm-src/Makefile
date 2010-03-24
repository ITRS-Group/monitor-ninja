# This file should be configured before running `make'.
# Uncomment or change the values that are relevant for your OS.

# The preferred C compiler (by default use the OS-specific default value).
# For BSD/OS, FreeBSD, Linux (all flavors), NetBSD, OpenBSD the default
# compiler is GNU C. 
# (Note please the politically correct ordering by alphabet ! :-)
#
# Use GNU C even if it's not the default compiler
#
#CC=gcc
#
# Use the standard ANSI C compiler on HP-UX even if it's not default
#
#CC=c89

#
# The system-dependent flags for the C compiler
#
# Default

CFLAGS_SYS= -O

# For GNU C
#
#CFLAGS_SYS= -O2
#
# For GNU C with long options support library (Linux etc.)
#
#CFLAGS_SYS= -O2 -D_GNU_SOURCE
#
# For GNU C on HP-UX/PA-RISC 1.1
#
#CFLAGS_SYS= -O2 -Wa,-w
#
# For the standard ANSI C on HP-UX
#
#CFLAGS_SYS= +O2 -D_HPUX_SOURCE

#
# The system-dependent libraries
#
# Defalut (for the BSD-style OSes)

LIBS_SYS= -lm

# For SystemV (such as SCO, UnixWare, Solaris, but _NOT_ Linux or HP-UX)
#
#LIBS_SYS= -lm -lsocket

CFLAGS= $(CFLAGS_SYS)
LIBS= $(LIBS_SYS)

pfm2afm: pfm2afm.o
	$(CC) $(CFLAGS) -o pfm2afm pfm2afm.o $(LIBS)

pfm2afm.o: pfm2afm.c pfm2afm.h
	$(CC) $(CFLAGS) -c pfm2afm.c
	
