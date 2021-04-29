# FindReplace
Basic search over text files for a string, replace it or just report the occurrences.


## Features
- Find files with specific text/string
- Search inside dir and subdirs
- Replace the found text
- Log with the results


## Installation
Just require the class

## A Simple Example
<?php
	$fr = new FindReplace\FindReplace\FindReplace;
	$fr->find('Some text')->replace('New text')->tree('/home/root/Dir/example')->logTo('/home/root/Dir')->go();