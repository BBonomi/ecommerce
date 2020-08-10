<?php
// Função para formatar valor do produto aula 112
use Hcode\Model\Cart;
use Hcode\Model\User;
function formatPrice($vlprice) { // Alterado Aula 121 6:21 (float $vlprice) para ($vlprice)
	if (! $vlprice > 0)
		$vlprice = 0;

	return number_format ( $vlprice, 2, ",", "." );
}
// Formato Data Aula 127 Pedidos-Admin
function formatDate($date) 
{
	return date ( 'd/m/Y', strtotime ( $date ) );
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
// Função Botão Carrinho Quantidade Aula 125 Meus Pedidos 15:07
function getCartNrQtd() {
	$cart = Cart::getFromSession ();

	$totals = $cart->getProductsTotals ();

	return $totals ['nrqtd'];
}
// Função Botão Carrinho Valor Subtotal Aula 125 Meus Pedidos 15:58
function getCartVlSubTotal() {
	$cart = Cart::getFromSession ();

	$totals = $cart->getProductsTotals ();

	return formatPrice ( $totals ['vlprice'] );
}
