<?php
require_once( dirname(__FILE__) .'/../simpleMP3Parse.class.php'); 
require_once( dirname(__FILE__) .'/../simpleFileParse.class.php');
require_once( dirname(__FILE__) .'/../simpleFunctions.php');

//Test - Basic Listing of files
/*
$folder = new simpleFileParse( 'files');
$folder->mode = 'advanced';
oops( $folder->getList() );


//Test - Basic Listing of files and folders
oops( $folder->getFolders() );


//Test - Basic Listing of files
oops( $folder->getFiles() );


//Test - Adding single filter include
$folder = new simpleFileParse( 'files');
$folder->includeExt('gif');
oops( $folder->getFiles() );

//Test - Adding multiple include filters
$folder->includeExt('m3u');
oops( $folder->getFiles() );

//Test - Adding single exclude filter
$folder = new simpleFileParse( 'files');
$folder->excludeExt('txt');
oops( $folder->getFiles() );

//Test - Adding multiple exclude filter
$folder = new simpleFileParse( 'files');
$folder->excludeExt('m3u');
oops( $folder->getFiles() );

//Test - Reading outside of root folder.
$folder = new simpleFileParse( 'files', '../');
*/

//Test simpleMP3FileParse
$folder = new simpleMP3Parse('files');
oops( $folder->getFiles() );