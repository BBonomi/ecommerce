<?php

// Classe Model
namespace Hcode;

class Model {
	// Metodos Getters e Setters
	// $values terão todos os valores dos campos dentro dos objetos
	private $values = [ ];
	// Metodo magico __call
	public function __call($name, $args) {
		$method = substr ( $name, 0, 3 ); // para saber se é o metodo get ou set
		$fieldName = substr ( $name, 3, strlen ( $name ) ); // descobrindo o nome do campo chamado

		// var_dump ( $method, $fieldName );
		// exit ();

		switch ($method) {
			case "get" :
				return $this->values [$fieldName];
				break;
			case "set" :
				$this->values [$fieldName] = $args [0];
				break;
		}
	}
	// Metodo set automatico todos os campos usuario linha 27 User.php
	public function setData($data = array ()) {
		foreach ( $data as $key => $value ) {
			$this->{"set" . $key} ( $value );
		}
	}
	// Metodo Sessão
	public function getValues() {
		return $this->values;
	}
}
