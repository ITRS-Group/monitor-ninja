
# Contributing

The developers at OP5 are very happy to accept and maintain patches, as long  
as the code change fits with the general concepts of the project.  
In general, we prefer patches to be in the form of pull requests on Github  
if the project is available there, otherwise you can email patches to OP5  
at [op5-users@lists.op5.com](op5-users@lists.op5.com).

Once you have submitted your pull request or email, the pull request will  
enter our internal verification process, where we might check the format of  
the code, the amount of testing needed, etc. After this process is done, you  
will be notified in the same place that you sent your patches.

## Hints when writing commits

-  Make commits of logical units
-  Check for unnecessary whitespace with "git diff --check" before committing
-  Do not check in commented out code or unneeded files
-  The first line of the commit message should be a short description and  
   should skip the full stop
-  One or more paragraphs, outlining the _what_ and the _why_ of the change.  
   That is; What changed? Why was the change necessary?
-  if you want your work included upstream, add a "Signed-off-by: Your Name  
   <you@example.com>" line to the commit message (or just use the option "-s"  
   when committing) to confirm that you agree to the [Developer's Certificate of Origin](https://developercertificate.org/)
-  Make sure that you have tests for the bug you are fixing if possible
-  Make sure that the test suite passes after your commits
-  Provide a meaningful commit message

A good example message fixing some specific part of the codebase:

```
reports: Create reports in linear time

Previously we used to parse every line 42 times, wasting resources and
human cycles by the score. This patch alters the report summation
calculations so that all figures are added up properly in a single run,
making report creation about 40 times faster. The fact that we do it
using slightly less memory and with fewer page faults is a nice bonus
too.

Before (best of 5):
$ /usr/bin/time php run-reports.php >/dev/null
56.18user 0.09system 0:58.01elapsed 96%CPU (0avgtext+0avgdata 55440maxresident)k
1632inputs+536outputs (6major+26172minor)pagefaults 0swaps

After (best of 5):
$ /usr/bin/time php run-reports.php >/dev/null
1.32user 0.01system 0:00.42elapsed 78%CPU (0avgtext+0avgdata 41376maxresident)k
16inputs+0outputs (0major+10744minor)pagefaults 0swaps

This fixes issue #4793.

Signed-off-by: Andreas Ericsson <ae@op5.se>
```

A (very) bad commit message looks like this:
```
build fix
it broke on solaris
```
The latter is a horrible message, because it doesn't tell us which part of  
the build broke, or why, or how the fixer came to the conclusion that the  
implemented fix was the best one, or what to look out for in the future.

## Pull requests

A pull request should preferably contain tests, we don't merge anything that  
isn't tested. If tests aren't included or the pull request causes build fails  
we will create internal tickets to troubleshoot and/or add tests and take it  
into consideration in the upcoming sprint review(s).

## PHP Coding Standard

To use on PHP projects that doesn't inherit another coding standard.
### Case and Capitalization

-  Use "//" or "/\* \*/", avoid "\#".
-  Use DocBlocks to describe the purpose when you create, change the
   behavior of a file, class or function.

### PHP Language Features
-  Avoid globals.
-  When using globals (when it cannot be avoided), use  
   "$GLOBALS['variable']`" over `"global $variable"` when inside a function scope.
-  Avoid extract().
-  Avoid eval().
-  Avoid variable variables.
-  Prefer class constants over defines.

### Spaces, Linebreaks and Indentation

-   Use tab (4 characters wide) for indentation.
-   Use Unix linebreaks ("\\n"), not MSDOS ("\\r\\n") or OS9 ("\\r").
-   Put a space after control keywords like `if`, `for` and `while`.
-   Don't put spaces after function names.
-   Put a space after commas in argument lists.
-   Put a space around operators like =, &lt;, `.`,` .=`, etc. Unary operators  
    (operators that operate on only one value), such as `++`,  
    should not have a space between the operator and the variable or number  
    they are operating on.
-   Parentheses should hug their contents.
-   Wrap code at 80 columns.
-   No trailing whitespaces.
-   Put an empty line between one method/function/class and the DocBlock
    of the following one.
-   Use empty lines to separate logical sections of code (see
    <http://www.alejandrodu.com/blog/empty-lines-and-semantics-in-source-code>).

### PHP Language Style

-   Use `<?php`, not any of the short forms. Omit the closing `?>` tag.
-   Prefer type-sensitive comparisons like `===` and `!==`.
-   Use "`else if`" (with a space) and not "`elseif`".
-   Use braces on the same line on an "`else`" or "`else if`" statement,  
    like this: `} else {`.
-   If-statements without braces are OK as long as the following  
    statement is on the **next** line intended by one tab-space.

-   Ternary Operator (\[condition\] `?` \[true-action\] `:`
    \[false-action\]) is encouraged where "appropriate", i.e. where the  
    statement is simple enough to be easy to understand.

-   Alternate control statement syntax like, "`if (`...`):` ...
    `endif;`" is allowed in places with a lot of mixed HTML and PHP  
    (like in views).

-   Early returns are highly encouraged. Rather than having a lot of  
    nestled conditions, use an early check high up in the function that  
    returns the appropriate value when nothing more needs to be done.
-   Use type hints for arguments in a function or method when you can,  
    i.e. when the argument is an object or an array.

### HTML Code

When generating HTML code from PHP there's a few things to take into
consideration:

-   Use HTML5-style, i.e. **never** use an end-tag where it's not needed.
-   Do not put an “\\n”at the end of the line. Let the browser or another  
    external tool format the resulting HTML code if you having problems  
    reading it.


### Single and Double Quotes

Use single and double quotes when appropriate. If you’re not evaluating  
anything in the string, use single quotes. You should almost never have  
to escape quotes in a string, because you can just alternate your quoting 
style, like so:
```
echo '<a href="/static/link" title="Yeah yeah!">Link name</a>';
echo "<a href='$link' title='$linktitle'>$linkname</a>";
```
