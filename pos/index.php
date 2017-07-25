<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
require_once __DIR__ . '/firebase.php';
require_once __DIR__ . '/push.php';
?>
<?php
        // Enabling error reporting
        error_reporting(-1);
        ini_set('display_errors', 'On');

        

        $firebase = new Firebase();
        $push = new Push();

        // optional payload
        $payload = array();
        $payload['team'] = 'India';
        $payload['score'] = '5.6';

        // notification title
        $title = isset($_GET['title']) ? $_GET['title'] : '';
        
        // notification message
        $message = isset($_GET['message']) ? $_GET['message'] : '';
        
        // push type - single user / topic
        $push_type = isset($_GET['push_type']) ? $_GET['push_type'] : '';
        
        // whether to include to image or not
        $include_image = isset($_GET['include_image']) ? TRUE : FALSE;


        $push->setTitle($title);
        $push->setMessage($message);
        if ($include_image) {
            $push->setImage('http://api.androidhive.info/images/minion.jpg');
        } else {
            $push->setImage('');
        }
        $push->setIsBackground(FALSE);
        $push->setPayload($payload);


        $json = '';
        $response = '';

        if ($push_type == 'topic') {
            $json = $push->getPush();
            $response = $firebase->sendToTopic('global', $json);
        } else if ($push_type == 'individual') {
            $json = $push->getPush();
            $regId = isset($_GET['regId']) ? $_GET['regId'] : '';
            $response = $firebase->send($regId, $json);
        }
        ?>
<HTML>
<HEAD>
<TITLE>Shop</TITLE>
<link href="style.css" type="text/css" rel="stylesheet" />
<script src="jquery-2.1.1.min.js" type="text/javascript"></script>
<script>
function showEditBox(editobj,id) {
	$('#frmAdd').hide();
	$(editobj).prop('disabled','true');
	var currentMessage = $("#message_" + id + " .message-content").html();
	var editMarkUp = '<textarea rows="5" cols="80" id="txtmessage_'+id+'">'+currentMessage+'</textarea><button name="ok" onClick="callCrudAction(\'edit\','+id+')">Save</button><button name="cancel" onClick="cancelEdit(\''+currentMessage+'\','+id+')">Cancel</button>';
	$("#message_" + id + " .message-content").html(editMarkUp);
}
function cancelEdit(message,id) {
	$("#message_" + id + " .message-content").html(message);
	$('#frmAdd').show();
}
function cartAction(action,product_code) {
	var queryString = "";
	if(action != "") {
		switch(action) {
			case "add":
				queryString = 'action='+action+'&productBarcode='+ product_code+'&quantity='+$("#qty_"+product_code).val();
			break;
			case "remove":
				queryString = 'action='+action+'&productBarcode='+ product_code;
			break;
			case "empty":
				queryString = 'action='+action;
			break;
			case "order":
				queryString = 'action='+action;
			break;
		}	 
	}
	jQuery.ajax({
	url: "ajax_action.php",
	data:queryString,
	type: "POST",
	success:function(data){
		$("#cart-item").html(data);
		if(action != "") {
			switch(action) {
				case "add":
					//$("#add_"+product_code).hide();
					//$("#added_"+product_code).show();
				break;
				case "remove":
					//$("#add_"+product_code).show();
					//$("#added_"+product_code).hide();
				break;
				case "empty":
					$(".btnAddAction").show();
					$(".btnAdded").hide();
				break;
			}	 
		}
	},
	error:function (){}
	});
}
</script>
</HEAD>
<BODY>
<div id="product-grid">
	<div class="txt-heading">Products</div>
	<?php
	$product_array = $db_handle->runQuery("SELECT * FROM products ORDER BY productId ASC");
	if (!empty($product_array)) { 
		foreach($product_array as $key=>$value){
	?>
		<div class="product-item">
			<form id="frmCart">
			<div class="product-image"><img  height="100px" width="100px" src="product-images/<?php echo $product_array[$key]["productImage"];  ?>"></div>
			<div><strong><?php echo $product_array[$key]["productName"]; ?></strong></div>
			<div class="product-price"><?php echo "$".$product_array[$key]["productPrice"]; ?></div>
			<div><input type="text" id="qty_<?php echo $product_array[$key]["productBarcode"]; ?>" name="quantity" value="1" size="2" />
			<?php
				$in_session = "0";
				if(!empty($_SESSION["cart_item"])) {
					$session_code_array = array_keys($_SESSION["cart_item"]);
				    if(in_array($product_array[$key]["productBarcode"],$session_code_array)) {
						$in_session = "1";
				    }
				}
			?>
			<input type="button" id="add_<?php echo $product_array[$key]["productBarcode"]; ?>" value="Add to cart" class="btnAddAction cart-action" onClick = "cartAction('add','<?php echo $product_array[$key]["productBarcode"]; ?>')" <?php if($in_session != "0") { ?>style="display:none" <?php } ?> />
			<input type="button" id="added_<?php echo $product_array[$key]["productBarcode"]; ?>" value="Added" class="btnAdded" <?php if($in_session != "1") { ?>style="display:none" <?php } ?> />
			</div>
			</form>
		</div>
	<?php
			}
	}
	?>
</div>
<div class="clear-float"></div>
<div id="shopping-cart">
<div class="txt-heading">Shopping Cart <a id="btnEmpty" class="cart-action" onClick="cartAction('empty','');">Empty Cart</a></div>
<div id="cart-item"></div>
</div>
<script>
$(document).ready(function () {
	cartAction('','');
})
</script>


</BODY>
</HTML>