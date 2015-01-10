<?php

include "free-way.php";

// input test data below
$freeWay = new FreeWayFileHost("downloadUrl","username","password",array());

// check if login worked
if ($freeWay->Verify(true) == (USER_IS_PREMIUM || USER_IS_FREE))
	echo "Login succeeded :)!\n";	
else
	echo "Login failed :/";
	
// output real download url
var_dump($freeWay->GetDownloadInfo());

?>
