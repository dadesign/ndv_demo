<?php

//Уровень сообщений об ошибках
//error_reporting(E_ALL);

//Старт сессии
session_start();

//Проверка версии PHP
if (version_compare(phpversion(), '5.3.0', '<')) {
die('Внимание! Для правильной работы скрипта необходим PHP версии 5.3.0 и выше.');
}

//Вывод заголовка с данными о кодировке страницы
header('Content-Type: text/html; charset=utf-8');

//Кодировка UTF-8 
setlocale(LC_ALL, 'Russian_Russia.65001');

//Устанавливаем временную зону по умолчанию для всех функций даты/времени в скрипте
date_default_timezone_set('Europe/Moscow');

//Запускаем счетчик времени
list($usec, $sec)=explode(" ", microtime());
$querytime_before=((float)$usec+(float)$sec);

/////////////////////////////////////////////////

//Подключение файла с настройками CMS
include_once(__DIR__.'/config.php');

//Подключение класса
include_once($_SERVER["DOCUMENT_ROOT"].$_CONFIG['dir_name'].'/libs/ndv-class.php');

/////////////////////////////////////////////////

//Инициализация переменных
if (!isset($_GET['action'])) {
$_GET['action']='';
}
if (!isset($_GET['sort'])) {
$_GET['sort']='id';
}
if (!isset($_GET['data_num'])) {
$_GET['data_num']='10';
}

//Инициализация класса
$ndvObj=new NdvClass($_CONFIG);

//Установка кодировки вывода записей из БД
mysql_query("SET NAMES 'utf8'"); 
mysql_query("SET CHARACTER SET 'utf8'");
mysql_query("SET SESSION collation_connection='utf8_general_ci'");

/////////////////////////////////////////////////

//Создать таблицу в БД
if ($_GET['action'] == 'create_tables') {
$ndvObj->CreateTablesDB($_CONFIG['dump_sql']);
if (!$ndvObj->_error) {
header("Location: ".$_SERVER["SCRIPT_NAME"]);
exit();
}
}

//Наполнить таблицу случайными данными
if ($_GET['action'] == 'random_data') {
$ndvObj->GenerateRandomData($_GET["maxnum"]);
if (!$ndvObj->_error) {
header('Location: '.$_SERVER['SCRIPT_NAME']);
exit();
}
}

//Удалить таблицы
if ($_GET['action'] == 'delete_tables') {
$ndvObj->DeleteTablesDB();
if (!$ndvObj->_error) {
header('Location: '.$_SERVER['SCRIPT_NAME']);
exit();
}
}

/////////////////////////////////////////////////

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
<TITLE>Типа админка</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
<LINK REL="stylesheet" TYPE="text/css" HREF="../css/bootstrap.css">
<LINK REL="stylesheet" TYPE="text/css" HREF="../css/bootstrap-theme.css">
<SCRIPT TYPE="text/javascript" SRC="../js/bootstrap.min.js"></SCRIPT>
</HEAD>
<BODY>

<?php

if ($ndvObj->_error) {
print "<DIV CLASS=\"alert alert-danger\" ROLE=\"alert\"><B>".$ndvObj->_error."</B></DIV>\n";
}

if ($ndvObj->TablesExistsDB() == 0) {
print "<DIV CLASS=\"alert alert-warning\" ROLE=\"alert\"><B>Ура! Скрипт работает, но база данных пустая. Необходимо <A HREF=\"index.php?action=create_tables\">создать таблицы</A>.</B></DIV>\n";
}
elseif ($ndvObj->TablesRowsDB() == 0) {
print "<DIV CLASS=\"alert alert-info\" ROLE=\"alert\"><B>Замечательно! Все таблицы были успешно созданы. Теперь их нужно наполнить данными.<BR>Выберите кол-во создаваемых записей: <A HREF=\"index.php?action=random_data&maxnum=1\">1</A>, <A HREF=\"index.php?action=random_data&maxnum=100\">100</A>, <A HREF=\"index.php?action=random_data&maxnum=500\">500</A>, <A HREF=\"index.php?action=random_data&maxnum=1000\">1000</A>, <A HREF=\"index.php?action=random_data&maxnum=5000\">5000</A>, <A HREF=\"index.php?action=random_data&maxnum=10000\">10000</A>, <A HREF=\"index.php?action=random_data&maxnum=50000\">50000</A></B></DIV>\n";
}
else {

?>

<nav class="navbar navbar-inverse">
<div class="container">
<div class="navbar-header">
<a class="navbar-brand" href="../">НДВ Демо</a>
</div>
<div id="navbar" class="collapse navbar-collapse">
<ul class="nav navbar-nav">
<li><a href="../">Публичная часть</a></li>
<li class="active"><a href="<?=$_CONFIG['dir_name'];?>/admin">Панель управления</A></li>
<li><a href="mailto:sergeyjr79@mail.ru">Обратная связь</a></li>
</ul>
</div>
</div>
</nav>

<DIV CLASS="alert alert-success" ROLE="alert"><B>Поздравляем! Все необходимые таблицы и записи были успешно созданы.</B></DIV>

<DIV>Теперь можно:</DIV>

<DIV>&nbsp;</DIV>

<DIV CLASS="list-group">
<A HREF="../" CLASS="list-group-item">Перейти в публичную часть</A>
<A HREF="#"><A HREF="index.php?action=delete_tables" ONCLICK="if (confirm('Вы действительно удалить все тестовые данные и повторить процесс установки?')) window.location.href=this.value; else return false;" CLASS="list-group-item">Удалить все тестовые записи</A>
</DIV>

<?php } ?>

<DIV ALIGN="center">Время генерации страницы: <SPAN ID="processing_time"><?=$ndvObj->ProcessingTime($querytime_before);?></SPAN> сек.</DIV>

</BODY>
</HTML>
<?php

//Закрытие соединения с MySQL
$ndvObj->closedb();

?>