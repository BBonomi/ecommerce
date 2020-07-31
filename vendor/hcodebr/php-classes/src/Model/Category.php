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
		Category::updateFile (); // Acrescentado aula 110
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
		Category::updateFile (); // Acrescentado aula 110
	}
	// Metodo atualizar lista de Categoria no rodapé do template do site principal Aula 110
	public static function updateFile() {
		$categories = Category::listAll ();
		$html = [ ];
		foreach ( $categories as $row ) {
			array_push ( $html, '<li><a href="/categories/' . $row ['idcategory'] . '">' . $row ['descategory'] . '</a></li>' );
		}
		file_put_contents ( $_SERVER ['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode ( '', $html ) );
	}
}
?>

	