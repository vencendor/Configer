<? 
 return array (
  'installed' => 'yes', // Установлено ли соединение [options|yes|no]
  'created' => "0", //Создано ли улучшение [options|true|0]
  'pers_data' => // Персональные данные
  array (
    'name' => 'Иван Иванович', // Введите свое имя
    'country' => '3', // Выберите страну проживания [options|0(RU)|1(BY)|2(UA)|3(KZ)]
	'sex' => "m" // Выберите пол [options|m(мужской)|j(женский)]
  ),
  'security_protocol' => 1, // Использовать защищеную связь [options|true|false]
  'news_page' => 20, // Сколько новостей выводить на странице
  'points' => // Времянные точки [dinamic]
  array(
	0 => 0.23, // Основная [static]
	1 => 0.4,
	2 => 0.67,
  )

) 
 ?>