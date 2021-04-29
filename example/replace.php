<?php
    /**
     * This example will find and replace all files with the searched text.
     * The search will be into '/base/dir', provided and all subdirs.
     * The log file with the result will be saved into 'path/to/save/log'
     */
    require_once('path/tho/class/FindReplace.php');

    $replace = new FindReplace\FindReplace\FindReplace;

	$replace->find('Text to be found')->replace('The content to substitute')->tree('/base/dir')->logTo('path/to/save/log')->go();
	
	echo '<pre>', print_r($replace->results), '</pre>';

    /*
        Output:
        Array
        (
            [Total matched files] => 18
            [Total updated files] => 18
            [Total failed updates] => 0
            [Total scanned files] => 263
            [Total scanned dirs] => 6
            [Limit exceded] => 
            [Log saved] => 1
        )

        Log saved:
        /base/dir/strings_replacement_1619705971.log
    */