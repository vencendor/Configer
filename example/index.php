<?

include "../configer.php";

$cf = new Configer("config.php");

$cf->deleteOptions = true;
$cf->addOptions = true;

$cf->showForm();

?>