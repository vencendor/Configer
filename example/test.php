<?

$a= array("0",0,"false",false);

foreach( $a as $v) {
	foreach( $a as $v1) {
		var_dump($v);
		echo " === ";
		var_dump($v1);
		echo "="; var_dump( boolval($v) == boolval($v1)); echo "</br>";
	}
	
	//boolval
}


?>