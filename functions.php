<?php
// Função para formatar valor do produto aula 112
function formatPrice(float $vlprice) {
	return number_format ( $vlprice, 2, ",", "." );
}