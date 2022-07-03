<?php
session_start();
require_once("dbcontroller.php");
$db_handle = new DBController();
if(!empty($_GET["action"])) { 
switch($_GET["action"]) {  // 接收action的訊號
	case "add": // 收到 "加入購物車" 訊號
		if(!empty($_POST["quantity"])) {
			$productByCode = $db_handle->runQuery("SELECT * FROM tblproduct WHERE code='" . $_GET["code"] . "'");
			$itemArray = array($productByCode[0]["code"]=>array('name'=>$productByCode[0]["name"], 'code'=>$productByCode[0]["code"], 'quantity'=>$_POST["quantity"], 'price'=>$productByCode[0]["price"], 'image'=>$productByCode[0]["image"]));
			
			if(!empty($_SESSION["cart_item"])) { // 若欲加入之產品已於購物車當中
				if(in_array($productByCode[0]["code"],array_keys($_SESSION["cart_item"]))) {
					foreach($_SESSION["cart_item"] as $k => $v) {
							if($productByCode[0]["code"] == $k) {
								if(empty($_SESSION["cart_item"][$k]["quantity"])) {
									$_SESSION["cart_item"][$k]["quantity"] = 0;
								}
								$_SESSION["cart_item"][$k]["quantity"] += $_POST["quantity"]; // 數量 = 原本數量 + 新增數量
							}
					}
				} else { 
					$_SESSION["cart_item"] = array_merge($_SESSION["cart_item"],$itemArray); 
				}
			} else { // 購物車尚未有這項產品
				$_SESSION["cart_item"] = $itemArray; //數量 = 新增數量
			}
		}
	break;
	case "remove": // 收到 "移除單一產品" 訊號
		if(!empty($_SESSION["cart_item"])) {
			foreach($_SESSION["cart_item"] as $k => $v) {
					if($_GET["code"] == $k)
						unset($_SESSION["cart_item"][$k]); // unset 就是將該產品的數量清除 (數量--> 0)(陣列中單一value)		
					if(empty($_SESSION["cart_item"]))
						unset($_SESSION["cart_item"]);
			}
		}
	break;
	case "empty": // 收到 "清空購物車" 訊號
		unset($_SESSION["cart_item"]); // 將整個購物車變數清除 (注意與上面不同)(整個陣列)
	case "checkup":
		
	break;	
}
}
?>
<HTML>
<HEAD>
<TITLE>簡易商城 Easy Store</TITLE>
<link href="style.css" type="text/css" rel="stylesheet" />
</HEAD>
<BODY>
<div id="shopping-cart">
<div class="txt-heading">購物車清單</div>
<a id="btnEmpty" href="index.php?action=empty">清空購物車</a>
<?php
if(isset($_SESSION["cart_item"])){ // 宣告最終的 總量、總價 變數
    $total_quantity = 0;
    $total_price = 0;
?>	
<table class="tbl-cart" cellpadding="10" cellspacing="1">
<tbody>
<tr>
<th style="text-align:left;">購買品項</th>
<th style="text-align:left;">品項代碼</th>
<th style="text-align:right;" width="10%">數量</th>
<th style="text-align:right;" width="10%">單價</th>
<th style="text-align:right;" width="10%">小計</th>
<th style="text-align:center;" width="7%">移除</th>
</tr>	
<?php		
    foreach ($_SESSION["cart_item"] as $item){ // 用for 迴圈一個一個看購物車中商品 (item代表單一商品)
        $item_price = $item["quantity"]*$item["price"]; // 總價 = 總數 * 單一售價 
		?>
				<tr>
				<td><img src="<?php echo $item["image"]; ?>" class="cart-item-image" /><?php echo $item["name"]; ?></td>
				<td><?php echo $item["code"]; ?></td>
				<td style="text-align:right;"><?php echo $item["quantity"]; ?></td>
				<td  style="text-align:right;"><?php echo "$ ".$item["price"]; ?></td>
				<td  style="text-align:right;"><?php echo "$ ". number_format($item_price,2); ?></td>  
				<td style="text-align:center;"><a href="index.php?action=remove&code=<?php echo $item["code"]; ?>" class="btnRemoveAction"><img src="icon-delete.png" alt="Remove Item" /></a></td>
				</tr>
				<?php
				$total_quantity += $item["quantity"]; // for迴圈每執行一個商品就將數量加到總數
				$total_price += ($item["price"]*$item["quantity"]); // 同上
		}
		?>

<tr>
<td colspan="2" align="right">總計:</td>
<td align="right"><?php echo $total_quantity; ?></td>
<td align="right" colspan="2"><strong><?php echo "$ ".number_format($total_price, 2); ?></strong></td>
<td></td>
</tr>
</tbody>
</table>

<a id="btnEmpty" href="index.php?action=checkup">結帳</a>		

  <?php
} else {
?>
<div class="no-records">購物車為空 快去逛逛吧!!</div>
<?php 
}
?>
</div>

<div id="product-grid">
	<div class="txt-heading">產品清單</div>
	<?php
	$product_array = $db_handle->runQuery("SELECT * FROM tblproduct ORDER BY id ASC");  // 從資料庫中將所有產品資料存入陣列中
	if (!empty($product_array)) {  
		foreach($product_array as $key=>$value){  // 利用for迴圈將陣列中產品顯示出來
	?>
		<div class="product-item">
			<!-- 這邊利用表單form結構顯示產品，當按下加入按鈕後，會發出訊號如下
			1.action = add 訊號 
			2.當前產品的代號(資料表的Code) 
			3.輸入的數量 
			-->
			<form method="post" action="index.php?action=add&code=<?php echo $product_array[$key]["code"]; ?>">
			<div class="product-image"><img src="<?php echo $product_array[$key]["image"]; ?>"></div>
			<div class="product-tile-footer">
			<div class="product-title"><?php echo $product_array[$key]["name"]; ?></div>
			<div class="product-price"><?php echo "$".$product_array[$key]["price"]; ?></div>
			<div class="cart-action"><input type="text" class="product-quantity" name="quantity" value="1" size="2" /><input type="submit" value="加入購物車" class="btnAddAction" /></div>
			</div>
			</form>
		</div>
	<?php
		}
	}
	?>
</div>
</BODY>
</HTML>