# Ninja - Ninja is Now Just Awesome

Ninja is a modern web GUI for Naemon.

## Requirements

Ninja requires the following system software to be installed:

- php 5.3+
- php-mysql
- php-cli 5.3+
- MySQL 5+

It also requires the following software:

- Naemon 1.0.4+ (needs to run on the same server) [https://github.com/naemon/naemon-core](https://github.com/naemon/naemon-core)
- Merlin [https://github.com/op5/merlin](https://github.com/op5/merlin)
- The Naemon project's fork of Livestatus [https://github.com/naemon/naemon-livestatus](https://github.com/naemon/naemon-livestatus)

## Installation

Ninja is a web application mainly written in php so in short words the
installation goes something like this:

1.  Download a release tarball from [https://github.com/op5/ninja/releases](https://github.com/op5/ninja/releases),
    or by checking out the project via git.

2.  Put Ninja and all of its files so that they are accessible from a webserver.
    Make sure that all Ninja requirements are installed, and that both the
    Merlin and the Livestatus broker modules are loaded by Naemon.

3.  Within the Ninja directory, type `make` and `make install`.

4.  Copy the Ninja dir to a suitable location. Will vary between installations.

        cp -a ninja /var/www/html/

    Ninja sadly mixes its static assets and PHP files (patches are welcome), so
    copying everything into the webroot is the common choice; rewriting a lot
    of paths is another choice. If you successfully get away with the second
    approach, email us (email address is mentioned later on) and we will buy
    you a beer.

5.  Configure your webserver. We provide an example config file for apache
    located at op5build/ninja.httpd-conf. The example below works for
    CentOS and RedHat.

        cp ninja/op5build/ninja.httpd-conf /etc/httpd/conf.d/ninja-httpd.conf
        vim /etc/httpd/conf.d/ninja-httpd.conf
        service httpd restart

6.  Configure Ninja.

    Edit the database connection settings in
    *ninja/application/config/database.php* and the path to the livestatus
    socket in */etc/op5/livestatus.yml* There are more configuration files
    located in ninja/application/config/ but you should normally not require to
    edit them, the same goes for ninja/index.php which contains config
    regarding error reporting and general paths to Ninja's files.

    If you want to use Ninja over http instead of https you should copy
      ninja/application/config/cookie.php
    to
      ninja/application/config/custom/
    and change `$config['secure'] = true;` to `false`.

7.  Setup the db tables required for Ninja by executing

      ninja/install_scripts/ninja_db_init.sh

8.  Configure /etc/op5/*.yml files; livestatus.yml should point to your
    livestatus socket. Look at the other files so they match your system.

9.  Point your browser to https://yourip/ninja and try your installation.

Congratulations! You now (hopefully) have a working Ninja installation

# Questions, feedback, patches

All form of communication is welcomed at op5's mailinglist,
op5-users@lists.op5.com. A subscription is needed in order to post, see
[http://lists.op5.com/mailman/listinfo/op5-users](http://lists.op5.com/mailman/listinfo/op5-users).

Check out [https://www.op5.org](https://www.op5.org) for more info about Ninja.

