<?php
// Função para formatar valor do produto aula 112
use Hcode\Model\User;
function formatPrice(float $vlprice) {
	return number_format ( $vlprice, 2, ",", "." );
}
// Função Checar Login Aula 119 Login
function checkLogin($inadmin = true) {
	return User::checkLogin ( $inadmin );
}
// Função Pegar o Nome do Usuario Aula 119 Login
function getUserName() {
	$user = User::getFromSession ();

	return $user->getdesperson ();
}