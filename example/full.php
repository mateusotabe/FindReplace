<?php
    /**
     * This example will find and replace all .txt files with the searched text.
     * The search will be into '/base/dir', provided and all subdirs.
     * With a cap for ammount of files to scan.
     * The log file with the result will be saved into 'path/to/save/log'.
     */
    require_once('path/tho/class/FindReplace.php');

    $replace = new FindReplace\FindReplace\FindReplace;

	$replace->find('Text to be found')
            ->type('txt')
            ->replace('The content to substitute')
            ->tree('/base/dir')
            ->limit(10000)
            ->logTo('path/to/save/log')
            ->go();
	
	echo '<pre>', print_r($replace->results), '</pre>';

    /*
        Output:
        Array
        (
            [Total matched files] => 41
            [Total updated files] => 41
            [Total failed updates] => 0
            [Total scanned files] => 8762
            [Total scanned dirs] => 28
            [Limit exceded] => 
            [Log saved] => 1
        )

        Log saved:
        /base/dir/strings_replacement_1619705971.log
    */