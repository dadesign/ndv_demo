<?php

class NdvClass {

//Переменные класса

public $_config='';

public $_error='';

public $_errors_array=array();

public $firstnames_array=array('Иван', 'Петр', 'Василий', 'Сергей', 'Александр', 'Максим', 'Иван', 'Артем', 'Дмитрий', 'Никита', 'Михаил', 'Даниил', 'Егор', 'Андрей');

public $lastnames_array=array('Путин', 'Пупкин', 'Смирнов', 'Иванов', 'Кузнецов', 'Попов', 'Соколов', 'Лебедев', 'Козлов', 'Новиков', 'Морозов', 'Петров', 'Волков', 'Соловьев', 'Васильев', 'Зайцев', 'Павлов', 'Семенов', 'Голубев', 'Виноградов', 'Богданов', 'Воробьев', 'Федоров', 'Михайлов', 'Беляев', 'Тарасов', 'Белов', 'Комаров', 'Орлов', 'Киселев', 'Макаров', 'Андреев', 'Ковалев', 'Ильин', 'Гусев', 'Титов', 'Кузьмин', 'Кудрявцев', 'Баранов', 'Куликов', 'Алексеев', 'Степанов', 'Яковлев', 'Сорокин', 'Сергеев', 'Романов', 'Захаров', 'Борисов', 'Королев', 'Герасимов', 'Пономарев', 'Григорьев', 'Лазарев', 'Медведев', 'Ершов', 'Никитин', 'Соболев', 'Рябов', 'Поляков', 'Цветков', 'Данилов', 'Жуков', 'Фролов', 'Журавлев', 'Николаев', 'Крылов', 'Максимов', 'Сидоров', 'Осипов', 'Белоусов', 'Федотов', 'Дорофеев', 'Егоров', 'Матвеев', 'Бобров', 'Дмитриев', 'Калинин', 'Анисимов', 'Петухов', 'Антонов', 'Тимофеев', 'Никифоров', 'Веселов', 'Филиппов', 'Марков', 'Большаков', 'Суханов', 'Миронов', 'Ширяев', 'Александров', 'Коновалов', 'Шестаков', 'Казаков', 'Ефимов', 'Денисов', 'Громов', 'Фомин', 'Давыдов', 'Мельников', 'Щербаков');

public $patronymic_array=array('Иванович', 'Петрович', 'Васильевич', 'Сергеевич', 'Александрович', 'Максимович', 'Иванович', 'Артемович', 'Дмитриевич', 'Никитович', 'Михайлович', 'Даниилович', 'Егорович', 'Андреевич');

public $address_array=array('На деревню дедушке', 'В город бабушке', 'Мой адрес не дом и не улица...');

public $domains_array=array('.ru', '.com', '.net', '.org');

protected $_connect=null;

protected $_selectdb=null;

/////////////////////////////////////////////////

//Конструктор

public function __construct($config=null) {
$this->_config=$config;
$this->_connect=$this->connect();
$this->_selectdb=$this->selectdb();
$this->_errors_array=array();
}

public function NdvClass() {
die('Запрещено использовать метод NdvClass(). Используйте конструктор PHP.');
}

/////////////////////////////////////////////////

//Соединение с MySQL
public function connect() {
$_connect=mysql_connect($this->_config['host'], $this->_config['user'], $this->_config['pass']);
if (!$_connect) {
die('Ошибка! Невозможно соединиться с MySQL. '.mysql_errno().': '.mysql_error());
}
return $_connect;
}

//Выбор базы данных
public function selectdb() {
$_selectdb=mysql_select_db($this->_config['db_name'], $this->_connect);
if (!$_selectdb) {
die('Ошибка! Невозможно подключиться к базе данных. '.mysql_errno().': '.mysql_error());
}
return $_selectdb;
}

//Запрос к базе данных
public function query($query) {
$result=mysql_query($query);
if (!$result) {
$this->_error='Ошибка! Невозможно выполнить запрос. '.mysql_errno().': '.mysql_error().'<BR>'.$query;
$this->_errors_array[]=$this->_error;
return false;
}
else {
return $result;
}
mysql_free_result($result);
}

//Закрытие соединения с MySQL
public function closedb() {
if ($this->_connect) {
mysql_close($this->_connect);
}
else {
die('Ошибка! Невозможно отключиться к базе данных. '.mysql_errno().': '.mysql_error());
}
}

//Метод экранирования переменных в запросе
public function QuoteSmart($string='') {
if (function_exists('get_magic_quotes_gpc')) {
$string=stripslashes($string);
}
if (!is_numeric($string)) {
$string="'".mysql_real_escape_string($string)."'";
}
return $string;
}

//Импорт дампа и создание таблиц в БД
public function CreateTablesDB($dump_sql) {
$dump_sql=str_replace($_SERVER['DOCUMENT_ROOT'], '', $dump_sql);
$file_content=file_get_contents($_SERVER['DOCUMENT_ROOT'].$dump_sql);
if (!$file_content) {
$this->_error='Ошибка! Не удалось получить содержимое файла '.basename($dump_sql);
$this->_errors_array[]=$this->_error;
}
else {
$queries_array=array_map('trim', explode(';', $file_content));
foreach ($queries_array as $key) {
$key=trim($key);
if ($key) {
$this->query($key);
}
}
}
}

//Удалить таблицы в БД
public function DeleteTablesDB() {
$tables_array=array(
$this->_config['tb_prefix'].'clients', 
$this->_config['tb_prefix'].'clients_rel'
);
$query='DROP TABLE '.implode(',', $tables_array);
$this->query($query);
}

//Проверка существования таблиц в БД
public function TablesExistsDB() {
$query='SELECT table_name FROM information_schema.columns WHERE table_schema='.$this->QuoteSmart($this->_config['db_name']).' AND table_name IN ('.$this->QuoteSmart($this->_config['tb_prefix'].'clients').', '.$this->QuoteSmart($this->_config['tb_prefix'].'clients_rel').')';
$result=$this->query($query);
$num_rows=mysql_num_rows($result);
return $num_rows;
}

//Проверка наличия записей в таблице
public function TablesRowsDB() {
$query='SELECT id FROM '.$this->_config['tb_prefix'].'clients LIMIT 1';
$result=$this->query($query);
$num_rows=mysql_num_rows($result);
return $num_rows;
}

/////////////////////////////////////////////////

//Функция генерации случайной строки из символов ASCII
private function makeRandomString($max=10) {
$i='0';
$possible_keys='abcdefghijklmnopqrstuvwxyz';
//$possible_keys.='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
//$possible_keys.='0123456789';
$keys_length=strlen($possible_keys);
$str='';
while ($i<=$max) {
$rand=mt_rand(1, $keys_length-1);
$str.=$possible_keys[$rand];
$i++;
}
return $str;
}

//Транслит строки в кодировке UFT-8
public function GetInTranslit($string) {
$replace=array("'"=>"", "`"=>"", "а"=>"a", "А"=>"a", "б"=>"b", "Б"=>"b", "в"=>"v", "В"=>"v", "г"=>"g", "Г"=>"g", "д"=>"d", "Д"=>"d", "е"=>"e", "Е"=>"e", "ж"=>"zh", "Ж"=>"zh", "з"=>"z", "З"=>"z", "и"=>"i", "И"=>"i", "й"=>"y", "Й"=>"y", "к"=>"k", "К"=>"k", "л"=>"l", "Л"=>"l", "м"=>"m", "М"=>"m", "н"=>"n", "Н"=>"n", "о"=>"o", "О"=>"o", "п"=>"p", "П"=>"p", "р"=>"r", "Р"=>"r", "с"=>"s", "С"=>"s", "т"=>"t", "Т"=>"t", "у"=>"u", "У"=>"u", "ф"=>"f", "Ф"=>"f", "х"=>"h", "Х"=>"h", "ц"=>"c", "Ц"=>"c", "ч"=>"ch", "Ч"=>"ch", "ш"=>"sh", "Ш"=>"sh", "щ"=>"sch", "Щ"=>"sch", "ъ"=>"", "Ъ"=>"", "ы"=>"y", "Ы"=>"y", "ь"=>"", "Ь"=>"", "э"=>"e", "Э"=>"e", "ю"=>"yu", "Ю"=>"yu", "я"=>"ya", "Я"=>"ya", "і"=>"i", "І"=>"i", "ї"=>"yi", "Ї"=>"yi", "є"=>"e", "Є"=>"e");
return $str=iconv('UTF-8', 'UTF-8//IGNORE', strtr($string, $replace));
}

/////////////////////////////////////////////////

//Генерация случайных данных в БД
public function GenerateRandomData($maxnum='100') {

$maxnum=trim($maxnum);
$maxnum=preg_replace('/[^0-9]/ui', '', $maxnum);
$maxnum=preg_replace('/[^\x20-\xFF]/', '', $maxnum);
$maxnum=trim($maxnum);

if ($maxnum < 100) {
$this->_error='Ошибка! Значение '.$maxnum.' слишком маленькое.';
$this->_errors_array[]=$this->_error;
}
elseif ($maxnum > 10000) {
$this->_error='Ошибка! Значение '.$maxnum.' слишком большое.';
$this->_errors_array[]=$this->_error;
}
else {

$query='INSERT INTO '.$this->_config['tb_prefix'].'clients VALUES ';
for ($i=0; $i<$maxnum; $i++) {
shuffle($this->firstnames_array);
shuffle($this->lastnames_array);
shuffle($this->patronymic_array);
shuffle($this->address_array);
$tmp_array=array();
$tmp_array['id']='';
$tmp_array['name']=$this->lastnames_array[0].' '.$this->firstnames_array[0].' '.$this->patronymic_array[0];
$tmp_array['address']=$this->address_array[0];
$tmp_array['add_date']=date('Y-m-d H:i:s', time());
$query.="('".implode("', '", $tmp_array)."')";
if (($i+1) < $maxnum) {
$query.=', ';
}
}

//Максимальный размер данных в одном запросе
$row=mysql_fetch_row($this->query("SHOW VARIABLES LIKE 'max_allowed_packet'"));
$max_allowed_packet=$row[1];

if (strlen($query) > $max_allowed_packet) {
$this->_error='Ошибка! Превышен размер запроса ('.round(strlen($query)/1024).'KB > '.round($max_allowed_packet/1024).'KB). Уменьшите кол-во вставляемых записей.';
$this->_errors_array[]=$this->_error;
}
else {
$result=$this->query($query);
}

////////////////////////

$clients_ids_array=array();
$result=$this->query('SELECT id, name FROM '.$this->_config['tb_prefix'].'clients');
if (mysql_num_rows($result)) {
while ($a_row=mysql_fetch_array($result)) {
$clients_ids_array[]=array('id'=>$a_row['id'], 'name'=>$a_row['name']);
}
}

if (count($clients_ids_array) > 0) {

$queries_array=array();
for ($i=0; $i<$maxnum; $i++) {
$tmp_array=array();
shuffle($clients_ids_array);
shuffle($this->domains_array);
$tmp_array['id']='';
$client_array=array_shift($clients_ids_array);
$tmp_array['client_id']=$client_array['id'];
$email_prefix=$this->GetInTranslit($client_array['name']);
$email_prefix=str_replace(' ', '_', $email_prefix);
for ($c=0; $c<=rand(0, 1); $c++) {
$tmp_array['email']=strtolower($email_prefix.'@'.$this->makeRandomString(rand(5, 10)).$this->domains_array[0]);
$tmp_array['phone']='+7 ('.rand(800, 999).') '.rand(100, 999).'-'.rand(10, 99).'-'.rand(10, 99);
$queries_array[]=$tmp_array;
}
}

$query='INSERT INTO '.$this->_config['tb_prefix'].'clients_rel VALUES ';
$i='1';
foreach ($queries_array as $key_array) {
$query.="('".implode("', '", $key_array)."')";
if ($i < count($queries_array)) {
$query.=', ';
}
$i++;
}

$result=$this->query($query);

}

}

}

/////////////////////////////////////////////////

//Вывод списка клиентов
public function GetClientsInfo($client_name, $sort, $limit, $delay_sec) {

if ($client_name) {
$client_name=trim($client_name);
$client_name=urldecode($client_name);
$client_name=preg_replace('/[^a-zа-я ]/ui', '', $client_name);
$client_name=preg_replace('/[^\x20-\xFF]/', '', $client_name);
$client_name=trim($client_name);
}

if ($sort) {
$sort=trim($sort);
$sort=urldecode($sort);
$sort=preg_replace('/[^a-z]/ui', '', $sort);
$sort=preg_replace('/[^\x20-\xFF]/', '', $sort);
$sort=trim($sort);
}

if ($limit) {
$limit=trim($limit);
$limit=preg_replace('/[^0-9]/ui', '', $limit);
$limit=preg_replace('/[^\x20-\xFF]/', '', $limit);
$limit=trim($limit);
}

//Делаем небольшую задержку между запросами для снижения нагрузки на сервер
if ($delay_sec > 0) {
usleep($delay_sec*1000000);
}

//Составление запроса
$query="SELECT t1.*, GROUP_CONCAT(DISTINCT t2.email ORDER BY t2.email ASC SEPARATOR ', ') AS emails, GROUP_CONCAT(DISTINCT t2.phone ORDER BY t2.phone ASC SEPARATOR ', ') AS phones FROM ".$this->_config['tb_prefix']."clients AS t1 INNER JOIN ".$this->_config['tb_prefix']."clients_rel AS t2 ON t1.id=t2.client_id";
if ($client_name) {
$query.=' WHERE t1.name LIKE '.$this->QuoteSmart('%'.$client_name.'%');
}
$query.=' GROUP BY t1.id';
if ($sort == 'name') {
$query.=" ORDER BY CASE WHEN t1.name LIKE 't1.name %' THEN 0 WHEN t1.name LIKE 't1.name%' THEN 1 WHEN t1.name LIKE '% t1.name%' THEN 2 ELSE 3 END, t1.name";
}
else {
$query.=' ORDER BY t1.id';
}
if ($limit) {
$query.=' LIMIT 0, '.$limit;
}

//print $query;

$result=$this->query($query);

$info_array=array();

if (mysql_num_rows($result)) {
while ($a_row=mysql_fetch_array($result)) {

$new_emails_array=array();
$emails_array=array_map('trim', explode(',', $a_row['emails']));
foreach ($emails_array as $key=>$val) {
$new_emails_array[]="<A HREF=\"mailto:".$val."\" TITLE=\"Написать письмо на этот email\">".$val."</A>";
}
$a_row['emails']=implode("<BR>", $new_emails_array);

$new_phones_array=array();
$phones_array=array_map('trim', explode(',', $a_row['phones']));
foreach ($phones_array as $key=>$val) {
$new_phones_array[]=$val;
}
$a_row['phones']=implode("<BR>", $new_phones_array);

$info_array[]=$a_row;

}
}

return $info_array;

}

/////////////////////////////////////////////////

//Подсчет времени генерации страницы
public function ProcessingTime($querytime_before) {
list($usec, $sec)=explode(' ', microtime());
$querytime_after=(float)$usec+(float)$sec;
$global_querytime=$querytime_after-$querytime_before;
return sprintf('%.5f', $global_querytime);
}

/////////////////////////////////////////////////

}

?>