<?php

namespace Hcode\Model;

use Hcode\Model;
use Hcode\DB\Sql;

// Classe Categoria
class Category extends Model {

	// Metodo Listar todas categorias aula 109
	public static function listAll() {
		$sql = new Sql ();
		return $sql->select ( "SELECT * FROM tb_categories ORDER BY descategory" );
	}
	// Metodo salvar categoria
	public function save() {
		$sql = new Sql ();
		$results = $sql->select ( "CALL sp_categories_save(:idcategory, :descategory)", array (
				":idcategory" => $this->getidcategory (),
				":descategory" => $this->getdescategory ()
		) );

		$this->setData ( $results [0] );
	}

	// Metodo get BD
	public function get($idcategory) {
		$sql = new Sql ();
		$results = $sql->select ( "SELECT *FROM tb_categories WHERE idcategory = :idcategory", [ 

				':idcategory' => $idcategory
		] );
		$this->setData ( $results [0] );
	}
	// Metodo DELETAR Categoria
	public function delete() {
		$sql = new Sql ();
		$sql->query ( "DELETE FROM tb_categories WHERE idcategory = :idcategory", [ 

				':idcategory' => $this->getidcategory ()
		] );
	}
}
?>

	