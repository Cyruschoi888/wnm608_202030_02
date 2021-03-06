<?php

include "../lib/php/functions.php";
include "../parts/templates.php";
include "../parts/meta.php";

$empty_product = (object) [
	"name"=>"",
	"condition"=>"",
	"price"=>"",
	"category"=>"",
	"description"=>"",
	"thumbnail"=>"",
	"images"=>"",

];








if(isset($_GET['id'])) {
try{

$conn = makePDOConn();

switch(@$_GET['action']) {


	case "update":
		$statement = $conn->prepare("UPDATE
		`products`
		SET
			`name`=?,
			`condition`=?,
			`price`=?,
			`category`=?,
			`description`=?,
			`thumbnail`=?,
			`images`=?,
			`date_modify`=NOW()
		WHERE `id`=?
		");


		$statement->execute([
			$_POST["product-name"],
			$_POST["product-condition"],
			$_POST["product-price"],
			$_POST["product-category"],
			$_POST["product-description"],
			$_POST["product-thumbnail"],
			$_POST["product-images"],
			$_GET['id']
		]);



		header("location:{$_SERVER['PHP_SELF']}?id={$_GET['id']}");
		break;
	case "create":
		$statement = $conn->prepare("INSERT INTO
		`products`
		(
			`name`,
			`condition`,
			`price`,
			`category`,
			`description`,
			`thumbnail`,
			`images`,
			`date_create`,
			`date_modify`
		)
		VALUES
		(?,?,?,?,?,?,?,NOW(),NOW())
		");
		$statement->execute([
			$_POST["product-name"],
			$_POST["product-condition"],
			$_POST["product-price"],
			$_POST["product-category"],
			$_POST["product-description"],
			$_POST["product-thumbnail"],
			$_POST["product-images"],
		]);
		$id = $conn->lastInsertId();

		header("location:{$_SERVER['PHP_SELF']}?id=$id");
		break;

	case "delete":
		$statement = $conn->prepare("DELETE FROM `products` WHERE `id`=?");
		$statement->execute([$_GET['id']]);

		header("location:{$_SERVER['PHP_SELF']}");
		break;
}

} catch(PDOException $e) {
	die($e->getMessage());
}
}







function makeProductForm($o) {

$id = $_GET['id'];
$addoredit = $id=='new' ? 'Add' : 'Edit';
$createorupdate = $id=='new' ? 'create' : 'update';
$deletebutton = $id=='new' ? "" : "<li class='flex-none'><a href='{$_SERVER['PHP_SELF']}?id=$id&action=delete'>Delete</a></li>";


$images = array_reduce(explode(",",$o->images),function($r,$o){
	return $r."<img src='$o'>";
});

$data_show = $id=='new' ? "" : <<<HTML
<div class="card soft">

<div class="product-main">
	<img src="$o->thumbnail" alt="">
</div>
<div class="product-thumbs">$images</div>

<h2>$o->name</h2>
<div>
	<strong>Condition</strong>
	<span>$o->condition</span>
</div>
<div>
	<strong>Price</strong>
	<span>&dollar;$o->price</span>
</div>
<div>
	<strong>Category</strong>
	<span>$o->category</span>
</div>
<div>
	<strong>Description</strong>
	<div>$o->description</div>
</div>
</div>
HTML;



echo <<<HTML
<div class="card soft">
	<nav class="nav-pills">
		<ul>
			<li class="flex-none"><a href="{$_SERVER['PHP_SELF']}">Back</a></li>
			<li class="flex-stretch"></li>
			$deletebutton
		</ul>
	</nav>
</div>
<div class="grid gap">
	<div class="col-xs-12 col-md-5">$data_show</div>
	<form method="post" action="{$_SERVER['PHP_SELF']}?id=$id&action=$createorupdate" class="col-xs-12 col-md-7">
		<div class="card soft">
		<h2>$addoredit Product</h2>
		<div class="form-control">
			<label for="product-name" class="form-label">Name</label>
			<input type="text" class="form-input" placeholder="A Product Name" id="product-name" name="product-name" value="$o->name">
		</div>
		<div class="form-control">
			<label for="product-condition" class="form-label">Condition</label>
			<input type="text" class="form-input" placeholder="A Product Condition" id="product-condition" name="product-condition" value="$o->condition">
		</div>		
		<div class="form-control">
			<label for="product-price" class="form-label">Price</label>
			<input type="number" class="form-input" placeholder="A Product Price" id="product-price" name="product-price" value="$o->price" step="0.01" min="0.01" max="1000">
		</div>
		<div class="form-control">
			<label for="product-category" class="form-label">Category</label>
			<input type="text" class="form-input" placeholder="A Product Category" id="product-category" name="product-category" value="$o->category">
		</div>
		<div class="form-control">
			<label for="product-description" class="form-label">Description</label>
			<textarea class="form-input" placeholder="A Product Description" id="product-description" name="product-description">$o->description</textarea>
		</div>
		<div class="form-control">
			<label for="product-thumbnail" class="form-label">Thumbnail</label>
			<input type="text" class="form-input" placeholder="A Product Thumbnail" id="product-thumbnail" name="product-thumbnail" value="$o->thumbnail">
		</div>
		<div class="form-control">
			<label for="product-images" class="form-label">Images</label>
			<input type="text" class="form-input" placeholder="A Product Images" id="product-images" name="product-images" value="$o->images">
		</div>

		<div class="form-control">
			<input type="submit" value="Submit" class="form-button">
		</div>
		</div>
	</form>
</div>
HTML;
}




?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Product Admin</title>

	<?php include "../parts/meta.php" ?>
</head>
<body>

	<header class="navbar">
		<div class="container display-flex">
			<div class="flex-stretch">
				
			</div>
			<nav class="nav flex-none">
				<ul class="display-flex">
					<li><a href="./index.php">Store</a></li>
					<li><a href="<?= $_SERVER['PHP_SELF'] ?>">Product List</a></li>
					<li><a href="<?= $_SERVER['PHP_SELF'] ?>?id=new">Add New Product</a></li>
				</ul>
			</nav>
		</div>
	</header>

	<div class="container">

	<?php

		$conn = makeConn();

		if(isset($_GET['id'])) {

			if($_GET['id']=="new") {
				makeProductForm($empty_product);
			} else {
				$rows = getRows($conn,"SELECT * FROM `products` WHERE `id`='{$_GET['id']}'");
				makeProductForm($rows[0]);
			}

		} else {


		?>
		<div class="card soft">
		<h2>Product Admin</h2>
		<p>Choose a product to edit, or click to view their individual pages.</p>

		<div class="itemlist">
		<?php

		$rows = getRows($conn,"SELECT * FROM `products`");

		echo array_reduce($rows,'makeListItemTemplate');

		?>
		</div>
		</div>
		<?php

		}

		?>
	</div>
      <hr width="50%">
<br>

  <br>



<footer class="footer-category">
  <div class="container display-flex">
    <div class="flex-none">
      <ul class="down-category">
        <li><h3>Categories</h3></li>
        <li><a href="product_list.php">Playstations</a></li>
        <li><a href="product_list.php">Nintendo Switch</a></li>
        <li><a href="product_list.php">Game Accessories</a></li>
      </ul>
    </div>
    <div class="product_list.php"></div>
    <div class="product_list.php">
      <ul class="product_list.php">
        <li><h3>Contact Us</h3></li>
        <li><p>Many good deals are waiting for you!</p></li>
      </ul>
    </div>
  </div>
    <div class="footer_title">
      <h3>©2020 Cyrus Choi All Rights Reserved</h3>

  </div>
</footer>
   	
</body>
</html>