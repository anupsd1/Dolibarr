<?php
/* Copyright (C) 2017  Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * Need to have following variables defined:
 * $object (invoice, order, ...)
 * $action
 * $conf
 * $langs
 */

// Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}

?>
<!-- BEGIN PHP TEMPLATE commonfields_add.tpl.php -->
<?php

$object->fields = dol_sort_array($object->fields, 'position');


//This is the original!!
/*
foreach($object->fields as $key => $val)
{
	// Discard if extrafield is a hidden field on form
        
	if (abs($val['visible']) != 1) continue;

	if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! $val['enabled']) continue;	// We don't want this field

	print '<tr id="field_'.$key.'">';
	print '<td';
	print ' class="titlefieldcreate';
	if ($val['notnull'] > 0) print ' fieldrequired';
	if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
	print '"';
	print '>';
	print $langs->trans($val['label']);
	print '</td>';
	print '<td>';
	if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
	elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
	else $value = GETPOST($key, 'alpha');
	print $object->showInputField($val, $key, $value, '', '', '', 0);
	print '</td>';
	print '</tr>';
 }
 //Till here!!!
 */
//if($object->element <> 'testinstruments')
if($object->element != 'dailywork')
{
 
    foreach($object->fields as $key => $val)
    {
	// Discard if extrafield is a hidden field on form
        if($val['label']=='Transfer From')
        {
            print '&emsp; <tr><td>';
            print '<p>Dear Mr. <input type="text" id="employeename" readonly><br>Sub: Transfer Order<br>';
            print 'You have been transferred from ';
            //print '</p></td><td>';
            if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
            elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
            else $value = GETPOST($key, 'alpha');
            print $object->showInputField($val, $key, $value, '', '', '', 0);
            //print '</td></tr>';
            
        }
        elseif($val['label']=='Transfer To')
        {
            print ' to: ';
            print '</p>';
            if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
            elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
            else $value = GETPOST($key, 'alpha');
            print $object->showInputField($val, $key, $value, '', '', '', 0);
            print '</td></tr>';
            
        }
        elseif($val['label']=='Report')
        {
            print '&ensp; <tr><td>';
            print '<p>You are requested to report Mr.';
            if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
            elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
            else $value = GETPOST($key, 'alpha');
            print $object->showInputField($val, $key, $value, '', '', '', 0);
            print ' Phone <input type="text" id="phonenum" readonly> ';
            
        }
        elseif($val['label']=='Report Date')
        {
            print 'on ';
            if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
            elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
            else $value = GETPOST($key, 'alpha');
            print $object->showInputField($val, $key, $value, '', '', '', 0);
            print '</p></td></tr>';
        }
        else
        {
            if (abs($val['visible']) != 1) continue;

            if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! $val['enabled']) continue;	// We don't want this field

            print '<tr id="field_'.$key.'">';
            print '<td';
            print ' class="titlefieldcreate';
            if ($val['notnull'] > 0) print ' fieldrequired';
            if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
            print '"';
            print '>';
            print $langs->trans($val['label']);
            print '</td>';
            print '<td>';
            if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
            elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
            else $value = GETPOST($key, 'alpha');
            print $object->showInputField($val, $key, $value, '', '', '', 0);
            print '</td>';
            print '</tr>';
        }
        //else
        
    }  
}          
 
else
{
    
    
            
    foreach($object->fields as $key => $val)
    {
                
	// Discard if extrafield is a hidden field on form
            if($val['label']=='Prodstock')
            {
                print '<table t1 border="1"><thead><th>Type</th><th>Length</th><th>Breadth</th><th>Exposure taken</th><th>Area</th></thead>';
                print '<tr class=\'work1\' pointer-events="none">';
                print '<td>';
                if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                else $value = GETPOST($key, 'alpha');
                //The following works but commented by Anup:
                print $object->showInputField($val, $key, $value, '', '', '', 0);
                //print $object2->showInputField($val, $key, $value);
                //print $object2->showInputField($val, $key, $value, '', '', '', 0);
                print '</td>';
	
            }
            elseif($val['label']=='Prodstock2')
            {
                //print '<table border="1">';
                print '<tr class=\'work3\' pointer-events="none">';
                print '<td>';
                if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                else $value = GETPOST($key, 'alpha');
                print $object->showInputField($val, $key, $value, '', '', '', 0);
                // print $object2->showInputField($val, $key, $value);
                print '</td>';
	
            }
            elseif($val['label']=='Prodstock1')
            {
                //print '<table border="1">';
                print '<tr class=\'work2\' pointer-events="none">';
                print '<td>';
                if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                else $value = GETPOST($key, 'alpha');
                print $object->showInputField($val, $key, $value, '', '', '', 0);
                // print $object2->showInputField($val, $key, $value);
                print '</td>';
	
            }
            elseif($val['label']=='Prodstock3')
            {
                //print '<table border="1">';
                print '<tr class=\'work4\' pointer-events="none">';
                print '<td>';
                if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                else $value = GETPOST($key, 'alpha');
                print $object->showInputField($val, $key, $value, '', '', '', 0);
                // print $object2->showInputField($val, $key, $value);
                print '</td>';
	
            }
    
            elseif(($val['label']=='Length1') || ($val['label']=='Length2') || ($val['label']=='Length3') || ($val['label']=='Length4') || ($val['label']=='Breadth1') || ($val['label']=='Breadth2') || ($val['label']=='Breadth3') || ($val['label']=='Breadth4'))
            {
                print '<td>';
                if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                else $value = GETPOST($key, 'alpha');
                print $object->showInputField($val, $key, $value, '', '', '', 0);
                // print $object2->showInputField($val, $key, $value);
                print '</td>';
            }
            elseif(($val['label']=='Exposure1') || ($val['label']=='Exposure2') ||($val['label']=='Exposure3') || ($val['label']=='Exposure4'))
            {
                print '<td>';
                if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                else $value = GETPOST($key, 'alpha');
                print $object->showInputField($val, $key, $value, '', '', '', 0);
                // print $object2->showInputField($val, $key, $value);
                print '</td>';
            }
            elseif($val['label']=='Area1')
            {
                print '<td>';
                if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                else $value = GETPOST($key, 'alpha');
                print $object->showInputField($val, $key, $value, '', '', '', 0);
                // print $object2->showInputField($val, $key, $value);
                print '</td>';
                
                print '<td><input type="button" class="add-row" value="Add"></td>';
                print '</tr>';
                //print '</table>';
            }
            elseif($val['label']=='Area2')
            {
                print '<td>';
                if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                else $value = GETPOST($key, 'alpha');
                print $object->showInputField($val, $key, $value, '', '', '', 0);
                // print $object2->showInputField($val, $key, $value);
                print '</td>';
                 print '<td><input type="button" class="add-row2" value="Add"></td>';
               
                print '</tr>';
                //print '</table>';
            }
            elseif($val['label']=='Area3')
            {
                print '<td>';
                if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                else $value = GETPOST($key, 'alpha');
                print $object->showInputField($val, $key, $value, '', '', '', 0);
                // print $object2->showInputField($val, $key, $value);
                print '</td>';
                 print '<td><input type="button" class="add-row3" value="Add"></td>';
               
                print '</tr>';
                //print '</table>';
            }
            
            elseif($val['label']=='Area4')
            {
                print '<td>';
                if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                else $value = GETPOST($key, 'alpha');
                print $object->showInputField($val, $key, $value, '', '', '', 0);
                // print $object2->showInputField($val, $key, $value);
                print '</td>';
                 print '<td><input type="button" class="add-row4" value="Add"></td>';
               
                print '</tr>';
                print '</table t1>';
            }
        //if(($val['label']!='Prodstock') || ($val['label']!='Prodstock1') || ($val['label']!='Prodstock2') || ($val['label']!='Length1') || ($val['label']!='Length2') || ($val['label']!='Length3') || ($val['label']!='Breadth1') || ($val['label']!='Breadth2') || ($val['label']=='Breadth3'))
        else    
        {
            if (abs($val['visible']) != 1) continue;

            if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! $val['enabled']) continue;	// We don't want this field

            print '<tr id="field_'.$key.'">';
            print '<td';
            print ' class="titlefieldcreate';
            if ($val['notnull'] > 0) print ' fieldrequired';
            if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
            print '"';
            print '>';
            print $langs->trans($val['label']);
            print '</td>';
            print '<td>';
            if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
            elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
            else $value = GETPOST($key, 'alpha');
            
            print $object->showInputField($val, $key, $value, '', '', '', 0);
            
            //print $object2->showInputField($val, $key, $value);
            print '</td>';
            print '</tr>';
        }
        
    }  
}
 
 /*   
    if($val['label']=='Prodstock')
        {
            print '<table border="1">'; 
            
            print '<tr>';
            
            print '<td>';
            if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
            elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
            else $value = GETPOST($key, 'alpha');
            print $object->showInputField($val, $key, $value, '', '', '', 0);
            print '</td>';
	
            $key->next;
            if($val['label']=='Length1')
            {
                print '<td>';
                if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                else $value = GETPOST($key, 'alpha');
                print $object->showInputField($val, $key, $value, '', '', '', 0);
                print '</td>';
                $key++;
                
                if($val['label']=='Breadth1')
                {
                    print '<td>';
                    if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
                    elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
                    else $value = GETPOST($key, 'alpha');
                    print $object->showInputField($val, $key, $value, '', '', '', 0);
                    print '</td>';
	
                }
            }
            
            print '</tr>';
            print '</table>';
        }
        else 
        {
     * */
    
        /*
        print '<table border="1">'; 
            
            print '<tr>';
            
            print '<td>';
            if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
            elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
            else $value = GETPOST($key, 'alpha');
            print $object->showInputField($val, $key, $value, '', '', '', 0);
            print '</td>';
            print '</tr>';
            print '</table>';
}
        
        
        /*
        else
        {
            if (abs($val['visible']) != 1) continue;

            if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! $val['enabled']) continue;	// We don't want this field
            print '<tr>';
            print '<table border="1">'; 
            print '<tr id="field_'.$key.'">';
            print '<td';
            print ' class="titlefieldcreate';
            if ($val['notnull'] > 0) print ' fieldrequired';
            if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
            print '"';
            print '>';
            //print $langs->trans($val['label']);
            print '</td>';
            print '<td>';
            if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
            elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
            else $value = GETPOST($key, 'alpha');
            print $object->showInputField($val, $key, $value, '', '', '', 0);
	
            print'</td></tr></table>';
            print '</td>';
            print '</tr>';
        
	
        }
         
         * 
         */

         

?>
<!-- END PHP TEMPLATE commonfields_add.tpl.php -->