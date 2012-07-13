#!/usr/bin/env python

import sys, subprocess
from nacoma.hooks import Change

reportable_types = ['host', 'service', 'hostgroup', 'servicegroup']

for line in sys.stdin:
    change = Change(line)
    if change.type not in reportable_types:
        continue
    username = subprocess.Popen(['/usr/bin/php', '/opt/monitor/op5/ninja/index.php', 'default/get_a_user'], stdout=subprocess.PIPE).communicate()[0]
    if change.is_renamed():
        output = subprocess.Popen(['/usr/bin/php', '/opt/monitor/op5/ninja/index.php', 'cli/handle_rename/%s/%s/%s' % (change.type, change.oldname, change.newname), username], stdout=subprocess.PIPE).communicate()[0]
    elif change.is_deleted():
        output = subprocess.Popen(['/usr/bin/php', '/opt/monitor/op5/ninja/index.php', 'cli/handle_deletion/%s/%s' % (change.type, change.oldname), username], stdout=subprocess.PIPE).communicate()[0]
