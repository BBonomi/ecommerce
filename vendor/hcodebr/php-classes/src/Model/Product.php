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
}
?>