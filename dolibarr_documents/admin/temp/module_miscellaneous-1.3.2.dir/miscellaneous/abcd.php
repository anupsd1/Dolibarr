<?php
//echo "HEloo";
require '../../main.inc.php';
include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
$allusers = new User($db);
//echo GETPOST('query');

$sql = "SELECT DISTINCT u.rowid, u.lastname";
$sql.= " FROM ".MAIN_DB_PREFIX."user as u";
$sql.=" WHERE lastname LIKE '%".GETPOST('query')."%'";
$result = $db->query($sql);
//echo $result;
$num = $db->num_rows($result);
//echo $num;

$i=0;
$myoutput = '<ul class="list-unstyled">';
$myoutput='';
while($i<$num)
{
    $obj=$db->fetch_object($result);
    //echo $obj->lastname;
    $myoutput.='<li>'.$obj->lastname.'</li>';
    $i++;
}
$myoutput.='</ul>';
echo $myoutput;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

