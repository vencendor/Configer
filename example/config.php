<? 
 return array (
  'user' => //Personal date
  array (
    'name' => 'Bill Smith',//Enter your name
    'country' => '0',//Country [options|0(RU)|1(EN)|2(GB)]
  ),
  'system' => //Tehnical date
  array (
    'con_type' => 'mysql',//Type of mysql connection [options|mysql|mysqli]
    'db_name' => '0',//Select db [options|0(Wordpress)|1(Joomla)|2(ModX)|3(OpenCart)]
    'security' => 1,//Use security system [options|0|1]
  ),
  'news_page' => '10',//Количество объявлений на странице
  'timer' => //Временые точки [dinamic]
  array (
    0 => '1',//первая точка [static]
    1 => '56.58',
    2 => '0.91',
  ),
) 
 ?>