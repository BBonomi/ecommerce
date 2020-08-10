<?php
session_start (); // iniciando a sessão
require_once ("vendor/autoload.php");
use Slim\Slim;

$app = new Slim ();

$app->config ( 'debug', true );
// Add aula 111 - Organizando rotas em arquivos na raiz site.php, admin.php, admin-user.php e admin-categories.php

require_once ("functions.php");
require_once ("site.php");
require_once ("administrativo.php"); // Mudei de admin.php para administrativo.php pois o wampserver acusa um erro em chamar admin
require_once ("admin-users.php");
require_once ("admin-categories.php");
require_once ("admin-products.php");
require_once ("admin-orders.php");
$app->run ();

?>