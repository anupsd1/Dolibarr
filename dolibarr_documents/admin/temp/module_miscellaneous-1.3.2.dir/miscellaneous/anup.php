<?php
$warehouseid = ($_POST['warehouseid1']?$_POST['warehouseid1']:$_GET['warehouseid1']);
$id = ($_POST['myvar1']?$_POST['myvar1']:$_GET['myvar1']);
$mynbpiece = ($_POST['nbpiece']?$_POST['nbpiece']:$_GET['nbpiece']);

//echo $warehouseid."  ".$myvar."  ".$mynbpiece;

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/productlot.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/productstockentrepot.class.php';
if (! empty($conf->productbatch->enabled)) require_once DOL_DOCUMENT_ROOT.'/product/class/productbatch.class.php';
if (! empty($conf->projet->enabled))
{
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
}

$langs->load("products");
$langs->load("orders");
$langs->load("bills");
$langs->load("stocks");
$langs->load("sendings");

global $user;

//echo $warehouseid."-".$myvar;
$object = new Product($db);
$result = $object->fetch($id);
$object->load_stock();
//if(is_numeric($mynbpiece))
if(is_numeric($mynbpiece))
{
$result=$object->correct_stock($user,$warehouseid, $mynbpiece, 1);

if($result>0)
{
    echo "Values Changed succesfully";
}
 else {
    echo "Unsuccessful";
 }    

}
else
    echo "NOT NUMBERIC";
//$prod = new Product($db);
 
//$prod->correct_stock_by_anup($user, $warehouseid, $mynbpiece);


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>