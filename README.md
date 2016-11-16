# Configer
Php Class to make changes in yours configuration files with siple form redactor

Using: 

<?

include "../configer.php";

$cf = new Configer("settings.php");

$cf->showForm();

?>


In your configuration file (settings.php for example ) need add comments.
All comment after definition of array element its title of this element and can contain also keys with posible values and other.

Allowed keys in comments: 


[options|opt1|...|optn] - specify possible values 

[hidden]   -  hide level 2 values to default for curent element

[dinamic]  -  alow add new elements

[static]  -   disallow delete element

See example

