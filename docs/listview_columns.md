# Customizing listview columns
## Listing hosts is nice, but I want more

The default view of hosts (and other objects) are designed to give you a good overview, but can be tweaked to fit your workflow. Read on for some examples of why listviews are the best way to present data.

## Start editing columns in your profile
We start by visiting the user page: click your name in the upper right corner of the screen. Now, you should edit the text field labelled *Table Hosts* under the *Columns in listview* heading.

## Understanding what's possible
For every example here, copy the text and enter it to the *Table Hosts* field. Don't forget to save the changes by submitting the form for each change you do, then reload the *Host Detail* view to see the changes take effect.

Allright, let's get started right away with some examples:

    "Name" = name

That's not very useful, is it? You just replaced all the regular columns with a single one, the host's **name**. Let's go back a step:

    default

Whenever you want to reset, just replace everything in the text area with **default** to get the original view back.

We will now try to define multiple columns:

    "State" = state, "Name" = name, "Output" = plugin_output

Here we see that every column definition is separated by a comma (**,**), every column label is quoted (**"Column"**) and the content of every column is on the right hand side of an equal sign (**=**). Don't forget to label every column. This is important enough to state again: Don't forget to **label every column**.

Let's continue by backtracing to **default** and adding links to every hostgroup our host is a member of

    default, "Groups" = groups

OK, that was useful, let's make each group name a link as well:

    default, "Groups" = implode( ", ", [ "<a href=\"/monitor/index.php/extinfo/details?hostgroup=" + urlencode(x) + "\">" + htmlescape(x) + "</a>" for x in groups ] )

Good for you, you just modified a large part of the user interface by builtin tools in Monitor! Read on to discover the whole collection of helpful tools at your hands.

## Complete guide to column definitions
Copy each of these examples into your GUI and see what effect you get out of it.

    "Properly labelled timestamp" = time(last_check)

    "Style list items individually" = implode(" --- ", groups)

    "Display sensitive data in HTML" = htmlescape(plugin_output)
    
    "Style each numeric list item" = "<span style='background-color: " + idx(state, "green", "yellow", "red") + "; padding: 2px'>" + state + "</span>"
    
These are some heavier examples that were mentioned during talks at our Gothenburg office (come visit us):

    "Services" = implode(" ", [ "<div style=\"display: inline-block; padding: 0 0.2em; margin: 1px 0; background: "+idx(x[2] * (x[1]+1),"#DDDDDD","#CCFFCC","#FFFFCC","#FFDDCC","#F7EEDD")+"\">" + "<a href=\"/ninja/index.php/extinfo/details?host="+urlencode(name)+"&service="+urlencode(x[0])+"\">"+htmlescape(x[0])+"</a>" + "</div>" for x in services_with_info] )
    
and
    
    "Services" = "<table><tr><th>Description</th><th>State</th></tr>" + implode( "", ["<tr><td style=\"background: "+idx(x[2] * (x[1]+1),"#DDDDDD","#CCFFCC","#FFFFCC","#FFDDCC","#FFEEDD")+"\">"+x[0]+"</td><td style=\"background: "+idx(x[2] * (x[1]+1),"#DDDDDD","#CCFFCC","#FFFFCC","#FFDDCC","#FFEEDD")+"\">"+idx(x[2] * (x[1]+1),"pending","ok","warning","critical","unknown")+"</td></tr>" for x in services_with_info]) + "</table>"

Together with what you already learned, this should be enough for you to do some really fancy stuff to your interface.

### Finding columns to display
You have already used columns such as *name*, *state* and *plugin_output*. The easiest way to find all other columns is by bringing up the **graphical filter builder** which is located in any listview: it's a filter icon located in the top right. Edit the filter to display

    [hosts] name = "monitor"
    
and anywhere outside the textarea. Now, click the dropdown that has the value of "name" - now you see all the columns available for hosts.

### Services, servicegroups, hostgroups
Until now, we've only worked with hosts and a host's properties. You can, however, continue to edit other object types such as services. We recommend you to read through this document again and replace "host" with "service" wherever you read it, but also keeping your newly acquired knowledge about filter columns in mind.

## I need more help
Feel free to post questions to the mailinglist [op5-users@lists.op5.com](mailto:op5-users@lists.op5.com) (register first at [http://lists.op5.com/mailman/listinfo/op5-users](http://lists.op5.com/mailman/listinfo/op5-users)). That same mailinglist would like to hear about what you just pulled off thanks to listviews. Thanks for helping us making a better product!

Happy admin'ing

/ op5
