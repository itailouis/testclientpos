<?php
session_start();
require_once("dbcontroller.php");
require_once("functions.php");
$db_handle = new DBController();

if(!empty($_POST["action"])) {
switch($_POST["action"]) {
	case "add":
		if(!empty($_POST["quantity"])) {
			$productByCode = $db_handle->runQuery("SELECT * FROM products WHERE productBarcode='" . $_POST["productBarcode"] . "'");
			$itemArray = array($productByCode[0]["productBarcode"]=>array('productName'=>$productByCode[0]["productName"], 'productBarcode'=>$productByCode[0]["productBarcode"], 'quantity'=>$_POST["quantity"], 'productPrice'=>$productByCode[0]["productPrice"],'productId'=>$productByCode[0]["productId"]));
			
			if(!empty($_SESSION["cart_item"])) {
				if(in_array($productByCode[0]["productBarcode"],$_SESSION["cart_item"])) {
					foreach($_SESSION["cart_item"] as $k => $v) {
							if($productByCode[0]["productBarcode"] == $k)
								$_SESSION["cart_item"][$k]["quantity"] = $_POST["quantity"];
					}
				} else {
					$_SESSION["cart_item"] = array_merge($_SESSION["cart_item"],$itemArray);
				}
			} else {
				$_SESSION["cart_item"] = $itemArray;
			}
		}
	break;
	case "remove":
		if(!empty($_SESSION["cart_item"])) {
			foreach($_SESSION["cart_item"] as $k => $v) {
					if($_POST["productBarcode"] == $k)
						unset($_SESSION["cart_item"][$k]);
					if(empty($_SESSION["cart_item"]))
						unset($_SESSION["cart_item"]);
			}
		}
	break;
	case "empty":
		unset($_SESSION["cart_item"]);
	break;	
    case "order":
		if(!empty($_SESSION["cart_item"])) {
			foreach($_SESSION["cart_item"] as $items) {

			//if(instock($id)<100){  

             $product = $db_handle->runQuery("INSERT INTO notifications(brif_message,subject,imageUri,product_name,createdAt) VALUES('Pliz reoder','Re Order','noimage.png','" . $items["productName"]. "',NOW())");		
			
			//}

			$product = $db_handle->runQuery("INSERT INTO sales(productId,quantity,createdAt) VALUES('" .$items["productId"]. "','" . $items["quantity"]. "',NOW())");		
			}
			unset($_SESSION["cart_item"]);
		}
	break;	
}
}


function instock($id){
$productCode = $db_handle->runQuery("SELECT SUM(quantity) as qua_sold FROM sales WHERE sales.productId='$id'");
//echo $productCode['qua_sold'];
return $productCode['qua_sold'];

}

?>
<?php
if(isset($_SESSION["cart_item"])){
    $item_total = 0;
?>	
<table cellpadding="10" cellspacing="1">
<tbody>
<tr>
<th><strong>Name</strong></th>
<th><strong>Code</strong></th>
<th><strong>Quantity</strong></th>
<th><strong>Price</strong></th>
<th><strong>Action</strong></th>
</tr>	
<?php		
    foreach ($_SESSION["cart_item"] as $item){
		?>
				<tr>
				<td><strong><?php echo $item["productName"]; ?></strong></td>
				<td><?php echo  $item["productId"]."". $item["productBarcode"]; ?></td>
				<td><?php echo $item["quantity"]; ?></td>
				<td align=right><?php echo "$".$item["productPrice"]; ?></td>
				<td><a onClick="cartAction('remove','<?php echo $item["productBarcode"]; ?>')" class="btnRemoveAction cart-action">Remove Item</a></td>
				</tr>
				<?php
        $item_total += ($item["productPrice"]*$item["quantity"]);
		}
		?>

<tr>
<td><input type="button" id="order" value="Order" class="btnAddAction cart-action" onClick = "cartAction('order','')"  /></td><td colspan="4" align=right><strong>Total:</strong> <?php echo "$".$item_total; ?></td>
</tr>
</tbody>
</table>		
  <?php
}
?>