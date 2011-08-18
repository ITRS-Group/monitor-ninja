#!/usr/bin/env python

import sys, subprocess

reportable_types = ['host', 'service', 'hostgroup', 'servicegroup']

class Change(object):
    __slots__ = ['type', 'id', 'oldname', 'newname', 'username', 'time', 'pre', 'post']

    def __init__(self, line):
        parts = line.split('\t')
        for part in parts:
            if part.startswith('object_type'):
                self.type = part[len("object_type='"):-1]
            elif part.startswith('object_id'):
                self.id = part[len("object_id='"):-1]
            elif part.startswith('oldname'):
                self.oldname = part[len("oldname='"):-1]
            elif part.startswith('newname'):
                self.newname = part[len("newname='"):-1]
            elif part.startswith('username'):
                self.username = part[len("username='"):-1]
            elif part.startswith('time'):
                self.time = part[len("time='"):-1]
            elif part.startswith('pre'):
                self.pre = part[len("pre='"):-1]
            elif part.startswith('post'):
                self.post = part[len("post='"):-1]

    def is_new(self):
        return change.newname and not change.oldname
    def is_deleted(self):
        return not change.newname and change.oldname
    def is_renamed(self):
        return change.newname and change.oldname and change.newname != change.oldname

for line in sys.stdin:
    change = Change(line)
    if change.type not in reportable_types:
        continue
    if change.is_renamed():
        output = subprocess.Popen(['/usr/bin/php', '/opt/monitor/op5/ninja/index.php', 'cli/handle_rename/%s/%s/%s' % (change.type, change.oldname, change.newname)], stdout=subprocess.PIPE).communicate()[0]
    elif change.is_deleted():
        output = subprocess.Popen(['/usr/bin/php', '/opt/monitor/op5/ninja/index.php', 'cli/handle_deletion/%s/%s' % (change.type, change.oldname)], stdout=subprocess.PIPE).communicate()[0]
