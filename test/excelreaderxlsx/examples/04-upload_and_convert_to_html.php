<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);
//include("../../livedata/connection.php");
require_once __DIR__.'/../src/SimpleXLSX.php';

echo '<h1>XLSX to HTML</h1>';

if (isset($_FILES['file'])) {
	
	if ( $xlsx = SimpleXLSX::parse( $_FILES['file']['tmp_name'] ) ) {

		echo '<h2>Parsing Result</h2>';
		echo '<table border="1" cellpadding="3" style="border-collapse: collapse">';

		$dim = $xlsx->dimension();
		$cols = $dim[0];

		foreach ( $xlsx->rows() as $k => $r ) {
//$cardnum = trim($r[1]);
//$qst = Run("select * from paymenthistory where batchid = '".$cardnum."'");
//$coun = rcount($qst);
$coun=1;	
	if($coun>0)
			{
			
			//		if ($k == 0) continue; // skip first row
			echo '<tr>';
			
				echo '<td>' . $r[ 0 ]. '</td>';
				

			
			echo '</tr>';
		}}
		echo '</table>';
	} else {
		echo SimpleXLSX::parseError();
	}
}
echo '<h2>Upload form</h2>
<form method="post" enctype="multipart/form-data">
*.XLSX <input type="file" name="file"  />&nbsp;&nbsp;<input type="submit" value="Parse" />
</form>';
