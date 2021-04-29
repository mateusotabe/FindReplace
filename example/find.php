<?php
    /**
     * This example will find all files with the searched text.
     * The search will be into the dir provided and all subdirs.
     * The log file with the result will be saved into the provided dir path
     */
    require_once('path/tho/class/FindReplace.php');

    $find = new FindReplace\FindReplace\FindReplace;

	$find->find('Text to be found')->tree('/base/dir')->go();
	
	echo '<pre>', print_r($find->results), '</pre>';

    /* 
        Output:
        Array
        (
            [Total matched files] => 15
            [Total updated files] => 0
            [Total failed updates] => 0
            [Total scanned files] => 26
            [Total scanned dirs] => 2
            [Limit exceded] => 
            [Log saved] => 1
        )
        
        Log saved:
        /base/dir/strings_replacement_1619705971.log
    */