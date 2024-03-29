<?php
if (($system_version = @file_get_contents('/etc/op5-monitor-release')) !== FALSE) { ?>
        <p>Current OP5 Monitor System version: <strong><?php echo str_replace('VERSION=', '', $system_version); ?></strong></p>
<?php } ?>

<h1>Service &amp; Support</h1>
<p>Detailed information regarding Service and Support is covered in the general mandatory ITRS Service and Support agreement. This needs to be in place prior to contacting ITRS Support.</p>
<p><strong>Note:</strong> If you have purchased this system trough an ITRS authorized dealer always contact your dealer first.</p>
<p>For fast support, proceed to <a href="https://support.itrsgroup.com" target="_blank" class="about">support.itrsgroup.com</a>
and fill out the support form.</p>

<h1>Request for Enhancement (RFE)</h1>
<p>Every RFE are of interest to us. You might not be alone with an idea or suggestion on a feature or other enhancements to the system. The RFE will be prioritized higher and come at a cheaper per-customer price if many customers have requested similar enhancements.</p>
<p>You can add your enhancement request at:
<a href="https://support.itrsgroup.com" target="_blank" class="about">support.itrsgroup.com</a></p>

<h1>Software Licensing Information</h1>
<p>The ITRS OP5 Monitor System and most of the added extra software packages included under the GNU General Public License (GPL) 2.0. The Apache web server is licensed under the Apache Software Foundation License.</p>
<p>Run this command to get the list of installed OP5 package licenses on the system:</p>
<code>op5-software-licenses --format list --list-format '%{NVR} %{URL}'</code>
<br>
<p>ITRS retains strict legal copyright on all software, be it scripts, source code or binaries, that are written from scratch by the ITRS development team. All rights are reserved.</p>

<p>For further questions regarding copyright and licensing, get in touch at:
<a href="https://www.itrsgroup.com/about/contact" target="_blank" class="about">www.itrsgroup.com/about/contact</a>.</p>
