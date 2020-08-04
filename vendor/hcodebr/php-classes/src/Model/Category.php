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
	// Metodo Aula 113 Produtos x Categoria
	public function getProducts($related = true) {
		$sql = new Sql ();
		if ($related === true) {
			return $sql->select ( "
					
				SELECT * FROM tb_products WHERE idproduct IN(
				SELECT a.idproduct
				FROM tb_products a
				INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
				WHERE b.idcategory = :idcategory
				);
					
			", [ 

					':idcategory' => $this->getidcategory ()
			] );
		} else {

			return $sql->select ( "
					
				SELECT * FROM tb_products WHERE idproduct NOT IN(
				SELECT a.idproduct
				FROM tb_products a
				INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
				WHERE b.idcategory = :idcategory
			);
					
			", [ 

					':idcategory' => $this->getidcategory ()
			] );
		}
	}
	// Metodo Add BD template admin/categories-products.html Aula 113
	public function addProduct(Product $product) {
		$sql = new Sql ();
		$sql->query ( "INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)", [ 

				':idcategory' => $this->getidcategory (),

				':idproduct' => $product->getidproduct ()
		] );
	}
	// Metodo Remove BD template admin/categories-products.html Aula 113
	public function removeProduct(Product $product) {
		$sql = new Sql ();
		$sql->query ( "DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [ 

				':idcategory' => $this->getidcategory (),

				':idproduct' => $product->getidproduct ()
		] );
	}
}
?>

	