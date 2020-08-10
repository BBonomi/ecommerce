<?php

namespace Hcode\Model;

use Hcode\Model;
use Hcode\DB\Sql;

// Classe Categoria
class Product extends Model {

	// Metodo Listar todos Produtos aula 111
	public static function listAll() {
		$sql = new Sql ();
		return $sql->select ( "SELECT * FROM tb_products ORDER BY desproduct" );
	}
	// Metodo para exibir fotos lista de produtos rodapé do site aula 112 V 7:55
	public static function checkList($list) {
		foreach ( $list as &$row ) {
			$p = new Product ();
			$p->setData ( $row );
			$row = $p->getValues ();
		}
		return $list;
	}

	// Metodo salvar Produto Aula 111
	public function save() {
		$sql = new Sql ();

		$results = $sql->select ( "CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array (

				":idproduct" => $this->getidproduct (),
				":desproduct" => $this->getdesproduct (),
				":vlprice" => $this->getvlprice (),
				":vlwidth" => $this->getvlwidth (),
				":vlheight" => $this->getvlheight (),
				":vllength" => $this->getvllength (),
				":vlweight" => $this->getvlweight (),
				":desurl" => $this->getdesurl ()
		) );

		$this->setData ( $results [0] );
	}

	// Metodo get BD Produto Aula 111
	public function get($idproduct) {
		$sql = new Sql ();
		$results = $sql->select ( "SELECT *FROM tb_products WHERE idproduct = :idproduct", [ 

				':idproduct' => $idproduct
		] );
		$this->setData ( $results [0] );
	}
	// Metodo DELETAR Produto Aula 111
	public function delete() {
		$sql = new Sql ();
		$sql->query ( "DELETE FROM tb_products WHERE idproduct = :idproduct", [ 

				':idproduct' => $this->getidproduct ()
		] );
	}
	// Metodo Foto Aula 111 video 25:57
	public function checkPhoto() {
		if (file_exists ( $_SERVER ['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . $this->getidproduct () . ".jpg" )) {

			$url = "/res/site/img/products/" . $this->getidproduct () . ".jpg";
		} else {

			$url = "/res/site/img/product.jpg";
		}

		return $this->setdesphoto ( $url );
	}
	public function getValues() {
		$this->checkPhoto ();

		$values = parent::getValues ();

		return $values;
	}
	// Metodo SetPhoto Aula 111 video 33:31
	public function setPhoto($file) {
		$extension = explode ( '.', $file ['name'] );

		$extension = end ( $extension );

		switch ($extension) {

			case "jpg" :

			case "jpeg" :
				$image = imagecreatefromjpeg ( $file ["tmp_name"] );
				break;

			case "gif" :
				$image = imagecreatefromgif ( $file ["tmp_name"] );
				break;

			case "png" :
				$image = imagecreatefrompng ( $file ["tmp_name"] );
				break;
		}

		$dist = $_SERVER ['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . $this->getidproduct () . ".jpg";

		imagejpeg ( $image, $dist );
		imagedestroy ( $image );

		$this->checkPhoto ();
	}
	// Metodo Aula 115 Detalhes do Produto
	public function getFromURL($desurl) {
		$sql = new Sql ();
		$rows = $sql->select ( "SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [ 
				':desurl' => $desurl
		] );
		$this->setData ( $rows [0] );
	}
	// Metodo exibir categoria do Produto Aula 115
	public function getCategories() {
		$sql = new Sql ();
		return $sql->select ( "
			SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct
		", [ 
				':idproduct' => $this->getidproduct ()
		] );
	}
	// Aula 130 Paginação e Busca Produtos
	public static function getPage($page = 1, $itemsPerPage = 10) {
		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql ();

		$results = $sql->select ( "				
				
			SELECT SQL_CALC_FOUND_ROWS *			
				
			FROM tb_products				
				
			ORDER BY desproduct				
				
			LIMIT $start, $itemsPerPage;				
				
		" );

		$resultTotal = $sql->select ( "SELECT FOUND_ROWS() AS nrtotal;" );

		return [ 

				'data' => $results,

				'total' => ( int ) $resultTotal [0] ["nrtotal"],

				'pages' => ceil ( $resultTotal [0] ["nrtotal"] / $itemsPerPage )
		];
	}
	public static function getPageSearch($search, $page = 1, $itemsPerPage = 10) {
		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql ();

		$results = $sql->select ( "
				
				
			SELECT SQL_CALC_FOUND_ROWS *				
				
			FROM tb_products				
				
			WHERE desproduct LIKE :search				
				
			ORDER BY desproduct				
				
			LIMIT $start, $itemsPerPage;				
				
		", [ 

				':search' => '%' . $search . '%'
		] );

		$resultTotal = $sql->select ( "SELECT FOUND_ROWS() AS nrtotal;" );

		return [ 

				'data' => $results,

				'total' => ( int ) $resultTotal [0] ["nrtotal"],

				'pages' => ceil ( $resultTotal [0] ["nrtotal"] / $itemsPerPage )
		];
	}
}
?>