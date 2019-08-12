<?php
//echo "HEloo";

require '../../main.inc.php';
include_once 'class/myusers.class.php';
include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
//$allusers = new User($db);
//echo GETPOST('query');

$sql = "SELECT u.rowid, u.lastname, u.signature, u.job";
$sql.= " FROM ".MAIN_DB_PREFIX."user as u";
$sql.=" WHERE lastname LIKE '%".GETPOST('query')."%'";
//echo $sql;
$result = $db->query($sql);
//echo $result;

$num = $db->num_rows($result);
//echo $num;

$obj=$db->fetch_object($result);
$empid = $obj->rowid;
global $user,$conf;

//Works:-
//$empgroup=$user->getGroups($empid);
$myuser = new MyUsers($db);
$empgroup = $myuser->getGroups($empid);

$newarr=array($obj, 'empgroup'=>$empgroup);

//echo $empgroup;
//This should be the OUTPUT!!!-
echo json_encode($newarr);
                
		
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

