<?php
//echo "Hello";
require '../../main.inc.php';
include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
$data = GETPOST('myvar');
//echo $data;
$sql = "SELECT g.rowid, g.entity, g.nom as name, g.note, g.datec, g.tms as datem";
$sql.= " FROM ".MAIN_DB_PREFIX."usergroup as g";
$sql.=" WHERE rowid=".$data;
$result = $db->query($sql);
$obj = $db->fetch_object($result);
//$supervisor = $obj->note;
$newvar = strip_tags($obj->note);
$string = html_entity_decode($newvar);
$db->free();
//echo $string;
//$db->free($sql);
//$string = preg_replace("/\s/", "", $string);
//global $user;
//$string = preg_replace('/\s/', '', $string);
$string = preg_replace(
    "/(\t|\n|\v|\f|\r| |\xC2\x85|\xc2\xa0|\xe1\xa0\x8e|\xe2\x80[\x80-\x8D]|\xe2\x80\xa8|\xe2\x80\xa9|\xe2\x80\xaF|\xe2\x81\x9f|\xe2\x81\xa0|\xe3\x80\x80|\xef\xbb\xbf)+/",
    "",
    $string
);
//$string = str_replace('/\s+/', '', $string);
//$string = preg_replace('~\x{00a0}~','',$string);
$sqls = "SELECT u.rowid, u.lastname, u.office_phone";
$sqls.=" FROM ".MAIN_DB_PREFIX."user as u";
$sqls.=" WHERE lastname LIKE '%".($string)."%'";
//echo ($string);
$results=$db->query($sqls);
$objs=$db->fetch_object($results);
$phonenumber = $objs->office_phone;
//echo $sqls;
$json_arr = array($string, $phonenumber);
echo json_encode($json_arr);

		
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

