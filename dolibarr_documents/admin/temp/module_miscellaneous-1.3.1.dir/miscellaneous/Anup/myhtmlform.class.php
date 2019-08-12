<?php

include_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

class CustomForm extends Form
{
        var $db;
        /**
	 * Constructor
	 *
	 * @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}
        
	/**
	 * Generic method to select a component from a combo list.
	 * This is the generic method that will replace all specific existing methods.
	 *
	 * @param 	string			$objectdesc			Objectclassname:Objectclasspath
	 * @param	string			$htmlname			Name of HTML select component
	 * @param	int				$preselectedvalue	Preselected value (ID of element)
	 * @param	string			$showempty			''=empty values not allowed, 'string'=value show if we allow empty values (for example 'All', ...)
	 * @param	string			$searchkey			Search criteria
	 * @param	string			$placeholder		Place holder
	 * @param	string			$morecss			More CSS
	 * @param	string			$moreparams			More params provided to ajax call
	 * @param	int				$forcecombo			Force to load all values and output a standard combobox (with no beautification)
	 * @return	string								Return HTML string
	 * @see selectForFormsList select_thirdparty
	 */
	function selectForForms($objectdesc, $htmlname, $preselectedvalue, $showempty='', $myvar='', $searchkey='', $placeholder='', $morecss='', $moreparams='', $forcecombo=0)
	{
		global $conf, $user;
                
		$objecttmp = null;

		$InfoFieldList = explode(":", $objectdesc);
		$classname=$InfoFieldList[0];
		$classpath=$InfoFieldList[1];
		if (! empty($classpath))
		{
			dol_include_once($classpath);
			if ($classname && class_exists($classname))
			{
				$objecttmp = new $classname($this->db);
			}
		}
		if (! is_object($objecttmp))
		{
			dol_syslog('Error bad setup of type for field '.$InfoFieldList, LOG_WARNING);
			return 'Error bad setup of type for field '.join(',', $InfoFieldList);
		}
                
                
		$prefixforautocompletemode=$objecttmp->element;
		if ($prefixforautocompletemode == 'societe') $prefixforautocompletemode='company';
		$confkeyforautocompletemode=strtoupper($prefixforautocompletemode).'_USE_SEARCH_TO_SELECT';	// For example COMPANY_USE_SEARCH_TO_SELECT

		dol_syslog(get_class($this)."::selectForForms", LOG_DEBUG);

		$out='';
		if (! empty($conf->use_javascript_ajax) && ! empty($conf->global->$confkeyforautocompletemode) && ! $forcecombo)
		{
			$objectdesc=$classname.':'.$classpath;
			$urlforajaxcall = DOL_URL_ROOT.'/core/ajax/selectobject.php';
			//if ($objecttmp->element == 'societe') $urlforajaxcall = DOL_URL_ROOT.'/societe/ajax/company.php';

			// No immediate load of all database
			$urloption='htmlname='.$htmlname.'&outjson=1&objectdesc='.$objectdesc.($moreparams?$moreparams:'');
			// Activate the auto complete using ajax call.
			$out.=  ajax_autocompleter($preselectedvalue, $htmlname, $urlforajaxcall, $urloption, $conf->global->$confkeyforautocompletemode, 0, array());
			$out.= '<style type="text/css">.ui-autocomplete { z-index: 250; }</style>';
			if ($placeholder) $placeholder=' placeholder="'.$placeholder.'"';
			$out.= '<input type="text" class="'.$morecss.'" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$preselectedvalue.'"'.$placeholder.' />';
		}
		else
		{
			// Immediate load of all database
			$out.=$this->selectForFormsList($objecttmp, $htmlname, $preselectedvalue, $showempty, $myvar, $searchkey, $placeholder, $morecss, $moreparams, $forcecombo);
		}
                

		return $out;
	}

	/**
	 * Output html form to select an object.
	 * Note, this function is called by selectForForms or by ajax selectobject.php
	 *
	 * @param 	Object			$objecttmp			Object
	 * @param	string			$htmlname			Name of HTML select component
	 * @param	int				$preselectedvalue	Preselected value (ID of element)
	 * @param	string			$showempty			''=empty values not allowed, 'string'=value show if we allow empty values (for example 'All', ...)
	 * @param	string			$searchkey			Search value
	 * @param	string			$placeholder		Place holder
	 * @param	string			$morecss			More CSS
	 * @param	string			$moreparams			More params provided to ajax call
	 * @param	int				$forcecombo			Force to load all values and output a standard combobox (with no beautification)
	 * @param	int				$outputmode			0=HTML select string, 1=Array
	 * @return	string								Return HTML string
	 * @see selectForForms
	 */
	function selectForFormsList($objecttmp, $htmlname, $preselectedvalue, $showempty='', $myvar='', $searchkey='', $placeholder='', $morecss='', $moreparams='', $forcecombo=0, $outputmode=0)
	{
                //echo $myvar;
		global $conf, $langs, $user;

		$prefixforautocompletemode=$objecttmp->element;
		if ($prefixforautocompletemode == 'societe') $prefixforautocompletemode='company';
		$confkeyforautocompletemode=strtoupper($prefixforautocompletemode).'_USE_SEARCH_TO_SELECT';	// For example COMPANY_USE_SEARCH_TO_SELECT
                
                if($objecttmp->element == "cameraandxray")
                {
                    $fieldstoshow='t.ref, t.ig';
                }
                elseif($objecttmp->element == "surveymeter" || $objecttmp->element =='dosimeter' ||  $objecttmp->element =='densitometer' ||  $objecttmp->element =='gammaareazonemonitor')
                {
                    $fieldstoshow='t.ref';
                }
                elseif($objecttmp->element == "stock")
                {
                    $fieldstoshow='t.ref';
                }
                elseif ($objecttmp->element == "usergroup") 
                {
                    $fieldstoshow = 't.nom';
                }
                else
                {
                    $fieldstoshow='t.label';
                }
		if (! empty($objecttmp->fields))	// For object that declare it, it is better to use declared fields ( like societe, contact, ...)
		{
			$tmpfieldstoshow='';
			foreach($objecttmp->fields as $key => $val)
			{
				if ($val['showoncombobox']) $tmpfieldstoshow.=($tmpfieldstoshow?',':'').'t.'.$key;
			}
			if ($tmpfieldstoshow) $fieldstoshow = $tmpfieldstoshow;
		}

		$out='';
		$outarray=array();

		$num=0;

		// Search data
                //condition given by Anup
            
            if(($objecttmp->element == 'surveymeter') || ($objecttmp->element == 'dosimeter') || ($objecttmp->element == 'gammaareazonemonitor') || ($objecttmp->element == 'densitometer'))
            {
                /*   
                $sqlforwarid = "SELECT x.rowid, x.ref FROM ".MAIN_DB_PREFIX .$objecttmp->table_element." as x";
                $sqlforwarid.=" WHERE site = '".$myvar."'";
                $resql=$this->db->query($sqlforwarid);
                 * 
                 */
                //$obj = $this->db->fetch_object($resql);
                
                $sqlforwarid = 'SELECT x.rowid, x.ref FROM '.MAIN_DB_PREFIX.'entrepot as x';
                $sqlforwarid.=' WHERE x.ref="'.$myvar.'" AND 1=1';
                $warid=$this->db->query($sqlforwarid);
                if($warid)
                {
                    $warid2 = (int)$warid;
                //$warid2 = $this->db->fetch_object($warid);
                //$this->db->free();
                    $obj = $this->db->fetch_object($warid2);
                    $out = '<input type="hidden" id="warehouseid" value='.$obj->rowid.'>';
                
                    
                }
                if(($objecttmp->element == 'surveymeter') || ($objecttmp->element == 'dosimeter'))
                {
                    $sql = "SELECT t.rowid, t.elid, ".$fieldstoshow." FROM ".MAIN_DB_PREFIX .$objecttmp->table_element." as t";
                }
                else
                {
                    $sql = "SELECT t.rowid, ".$fieldstoshow." FROM ".MAIN_DB_PREFIX .$objecttmp->table_element." as t";
                }
                 $sql.=" WHERE site = '".$obj->rowid."'";
                 //return $sql;
                $resql=$this->db->query($sql);
                
                //$resql=$this->db->query($sqlforwarid);
		//if ($ressql)
                if($resql)
		{
			if (! $forcecombo)
			{
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
				$out .= ajax_combobox($htmlname, null, $conf->global->$confkeyforautocompletemode);
			}

			// Construct $out and $outarray
			$out.= '<select id="'.$htmlname.'" class="flat'.($morecss?' '.$morecss:'').'"'.($moreparams?' '.$moreparams:'').' name="'.$htmlname.'">'."\n";

			// Warning: Do not use textifempty = ' ' or '&nbsp;' here, or search on key will search on ' key'. Seems it is no more true with selec2 v4
			$textifempty='&nbsp;';

			//if (! empty($conf->use_javascript_ajax) || $forcecombo) $textifempty='';
			if (! empty($conf->global->$confkeyforautocompletemode))
			{
				if ($showempty && ! is_numeric($showempty)) $textifempty=$langs->trans($showempty);
				else $textifempty.=$langs->trans("All");
			}
			if ($showempty) $out.= '<option value="-1">'.$textifempty.'</option>'."\n";

			$num = $this->db->num_rows($resql);
                        //return $num;
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					$label='';
					$tmparray=explode(',', $fieldstoshow);
					foreach($tmparray as $key => $val)
					{
						$val = preg_replace('/t\./','',$val);
						$label .= (($label && $obj->$val)?' - ':'').$obj->$val;
					}
					if (empty($outputmode))
					{
						if ($preselectedvalue > 0 && $preselectedvalue == $obj->rowid)
						{
                                                        if($obj->elid)
                                                        {
                                                            $out.= '<option value="'.$obj->rowid.'" selected>'.$obj->elid."-".$label.'</option>';
                                                        }
                                                        else
                                                        {
                                                            $out.= '<option value="'.$obj->rowid.'" selected>'.$label.'</option>';
                                                        }
                                                    
						}
						else
						{
                                                        if($obj->elid)
                                                        {
                                                            $out.= '<option value="'.$obj->rowid.'">'.$obj->elid."-".$label.'</option>';
                                                        }
                                                        else
                                                        {
                                                            $out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
                                                        }
						}
					}
					else
					{
						array_push($outarray, array('key'=>$obj->rowid, 'value'=>$label, 'label'=>$label));
					}

					$i++;
					if (($i % 10) == 0) $out.="\n";
				}
			}

			$out.= '</select>'."\n";
		}
		else
		{
			dol_print_error($this->db);
		}

		$this->result=array('nbofelement'=>$num);

		if ($outputmode) return $outarray;
		return $out;
                

            }
            elseif($objecttmp->element =='stockmouvement') 
            {
                $textifempty='&nbsp;';

                $sqlforwarid = "SELECT x.rowid, x.ref, x.entity FROM ".MAIN_DB_PREFIX ."entrepot as x";
                $sqlforwarid.=" WHERE ref = '".$myvar."'";
                $warid=$this->db->query($sqlforwarid);
                $obj = $this->db->fetch_object($warid);
                $out = '<input type="hidden" id="warehouseid" value='.$obj->rowid.'>';
                
                //$resql=$this->db->query($sqlforwarid);
		//if ($ressql)
                /*
                if($resql)
		{
			if (! $forcecombo)
			{
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
				$out .= ajax_combobox($htmlname, null, $conf->global->$confkeyforautocompletemode);
			}

			// Construct $out and $outarray
			$out.= '<select id="'.$htmlname.'" class="flat'.($morecss?' '.$morecss:'').'"'.($moreparams?' '.$moreparams:'').' name="'.$htmlname.'">'."\n";

			// Warning: Do not use textifempty = ' ' or '&nbsp;' here, or search on key will search on ' key'. Seems it is no more true with selec2 v4
			$textifempty='&nbsp;';

			//if (! empty($conf->use_javascript_ajax) || $forcecombo) $textifempty='';
			if (! empty($conf->global->$confkeyforautocompletemode))
			{
				if ($showempty && ! is_numeric($showempty)) $textifempty=$langs->trans($showempty);
				else $textifempty.=$langs->trans("All");
			}
					$objp = $this->db->fetch_object($resql);
					//$label='';
                                        $label='';
                                        $out.= '<option value="'.$obj->rowid.'" selected>'.($objp->ref).'</option>';
						
					
			$out.= '</select>'."\n";
		}
		else
		{
			dol_print_error($this->db);
		}

		//$this->result=array('nbofelement'=>$num);

		if ($outputmode) return $outarray;
		return $out;
                */
                
                /*
                 * IMPORTANT!!-
                 * */
                $sql = "SELECT p.rowid as rowid, p.ref, p.label as produit, p.fk_product_type as type, p.pmp as ppmp, p.price, p.price_ttc, p.entity,";
			$sql.= " ps.reel as value";
			$sql.= " FROM ".MAIN_DB_PREFIX."product_stock as ps, ".MAIN_DB_PREFIX."product as p";
			$sql.= " WHERE ps.fk_product = p.rowid";
			$sql.= " AND ps.reel <> 0";	// We do not show if stock is 0 (no product in this warehouse)
			$sql.= " AND ps.fk_entrepot = ".$obj->rowid;
                        dol_syslog('List products', LOG_DEBUG);
			$resql = $this->db->query($sql);
                        
			if ($resql)
			{
				$num = $this->db->num_rows($resql);
				$i = 0;
				$var=True;
                                $out.= '<select id ="<select id="'.$htmlname.'" class="'.$htmlname.'" name="'.$htmlname.'">'."\n";
                                $out.= '<option value="-1">'.$textifempty.'</option>'."\n";

				while ($i < $num)
				{
					$objp = $this->db->fetch_object($resql);

					// Multilangs
					if (! empty($conf->global->MAIN_MULTILANGS)) // si l'option est active
					{
						$sql = "SELECT label";
						$sql.= " FROM ".MAIN_DB_PREFIX."product_lang";
						$sql.= " WHERE fk_product=".$objp->rowid;
						$sql.= " AND lang='". $langs->getDefaultLang() ."'";
						$sql.= " LIMIT 1";

						$result = $this->db->query($sql);
						if ($result)
						{
							$objtp = $this->db->fetch_object($result);
							if ($objtp->label != '') $objp->produit = $objtp->label;
						}
					}


					//print '<td>'.dol_print_date($objp->datem).'</td>';
					
                                        
                                        //print '<tr class="oddeven">'; 
                                        
                                        
                                        /*Was originally not commented!! 
                                        
                                        print "<td>";
					$productstatic->id=$objp->rowid;
                    $productstatic->ref = $objp->ref;
                    $productstatic->label = $objp->produit;
					$productstatic->type=$objp->type;
					$productstatic->entity=$objp->entity;
					//print $productstatic->getNomUrl(1,'stock',16);
					print '</td>';
                                        */
					

                                        // Label
					/* Was originally not commented:
                                         * print '<td>'.$objp->produit.'</td>';
                                         */
                                        $out.= '<option value="'.$objp->rowid.'">'.$objp->produit.'</option>';
                                        /*
					print '<td align="right">';
					$valtoshow=price2num($objp->value, 'MS');
					print empty($valtoshow)?'0':$valtoshow;
					print '</td>';
					$totalunit+=$objp->value;

                    // Price buy PMP
					print '<td align="right">'.price(price2num($objp->ppmp,'MU')).'</td>';

                    // Total PMP
					print '<td align="right">'.price(price2num($objp->ppmp*$objp->value,'MT')).'</td>';
					$totalvalue+=price2num($objp->ppmp*$objp->value,'MT');

                    // Price sell min
                    if (empty($conf->global->PRODUIT_MULTIPRICES))
                    {
                        $pricemin=$objp->price;
                        print '<td align="right">';
                        print price(price2num($pricemin,'MU'),1);
                        print '</td>';
                        // Total sell min
                        print '<td align="right">';
                        print price(price2num($pricemin*$objp->value,'MT'),1);
                        print '</td>';
                    }
                    $totalvaluesell+=price2num($pricemin*$objp->value,'MT');
                                         * *
                                         

                    if ($user->rights->stock->mouvement->creer)
					{
						print '<td align="center"><a href="'.DOL_URL_ROOT.'/product/stock/product.php?dwid='.$object->id.'&id='.$objp->rowid.'&action=transfert&backtopage='.urlencode($_SERVER["PHP_SELF"].'?id='.$id).'">';
						print img_picto($langs->trans("StockMovement"),'uparrow.png','class="hideonsmartphone"').' '.$langs->trans("StockMovement");
						print "</a></td>";
					}

					if ($user->rights->stock->creer)
					{
						print '<td align="center"><a href="'.DOL_URL_ROOT.'/product/stock/product.php?dwid='.$object->id.'&id='.$objp->rowid.'&action=correction&backtopage='.urlencode($_SERVER["PHP_SELF"].'?id='.$id).'">';
						print $langs->trans("StockCorrection");
						print "</a></td>";
					}

					$out.="</tr>";
					$i++;
				}
                                         * 
                                         */
                                
                                $i++;
		
                        }
                        $out.='</select>';
                        //return $out;
                        
               }	
               $this->result=array('nbofelement'=>$num);

                    if ($outputmode) return $outarray;
                    return $out;
                
                 
            }
            else
            {
                
                $sql = "SELECT t.rowid, ".$fieldstoshow." FROM ".MAIN_DB_PREFIX .$objecttmp->table_element." as t";
                if ($objecttmp->ismultientitymanaged == 2)
                    if (!$user->rights->societe->client->voir && !$user->societe_id) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
                $sql.= " WHERE 1=1";
                if(! empty($objecttmp->ismultientitymanaged)) $sql.= " AND t.entity IN (".getEntity($objecttmp->table_element).")";
                if ($objecttmp->ismultientitymanaged == 1 && ! empty($user->societe_id))
                {
                    if ($objecttmp->element == 'societe') $sql.= " AND t.rowid = ".$user->societe_id;
				else $sql.= " AND t.fk_soc = ".$user->societe_id;
                }
                if ($searchkey != '') $sql.=natural_search(explode(',',$fieldstoshow), $searchkey);
                if ($objecttmp->ismultientitymanaged == 2)
		if (!$user->rights->societe->client->voir && !$user->societe_id) $sql.= " AND t.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
                $sql.=$this->db->order($fieldstoshow,"ASC");
                 //$sql.=$this->db->plimit($limit, 0);
                //}
                //else
                //{
                    /*
                    $mywarid='';
                    $sqlforwarid = "SELECT x.rowid FROM ".MAIN_DB_PREFIX ."ENTREPOT as x";
                    $sqlforwarid.=" WHERE x.ref = '".$myvar."'";
                    $warid=$this->db->query($sqlforwarid);
                    //$sql = "SELECT t.rowid ".$fieldstoshow." FROM ".MAIN_DB_PREFIX .$objecttmp->table_element." as t";
                    //$sql = "SELECT t.label, t.value FROM ".MAIN_DB_PREFIX .$objecttmp->table_element." as t";
                    //$sql = "SELECT t.fk_product FROM ".MAIN_DB_PREFIX ." as t";
                    //$sql = "SELECT t.label FROM ".MAIN_DB_PREFIX .$objecttmp->table_element." as t inner join ".MAIN_DB_PREFIX ."entrepot as y on t.fk_entrepot=y.ref";
                    
                    //$sql.= " WHERE t.fk_entrepot='".$myvar."'";
                    //$sql.=" WHERE 1=1";
                   $sql = "SELECT label";
						$sql.= " FROM ".MAIN_DB_PREFIX."product_lang";
						$sql.= " WHERE fk_product=1";
						
                     */
                
                $resql=$this->db->query($sql);
                //$resql=$this->db->query($sqlforwarid);
		//if ($ressql)
                if($resql)
		{
			if (! $forcecombo)
			{
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
				$out .= ajax_combobox($htmlname, null, $conf->global->$confkeyforautocompletemode);
			}

			// Construct $out and $outarray
			$out.= '<select id="'.$htmlname.'" class="flat'.($morecss?' '.$morecss:'').'"'.($moreparams?' '.$moreparams:'').' name="'.$htmlname.'">'."\n";

			// Warning: Do not use textifempty = ' ' or '&nbsp;' here, or search on key will search on ' key'. Seems it is no more true with selec2 v4
			$textifempty='&nbsp;';

			//if (! empty($conf->use_javascript_ajax) || $forcecombo) $textifempty='';
			if (! empty($conf->global->$confkeyforautocompletemode))
			{
				if ($showempty && ! is_numeric($showempty)) $textifempty=$langs->trans($showempty);
				else $textifempty.=$langs->trans("All");
			}
			if ($showempty) $out.= '<option value="-1">'.$textifempty.'</option>'."\n";

			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					$label='';
					$tmparray=explode(',', $fieldstoshow);
					foreach($tmparray as $key => $val)
					{
						$val = preg_replace('/t\./','',$val);
						$label .= (($label && $obj->$val)?' - ':'').$obj->$val;
					}
					if (empty($outputmode))
					{
						if ($preselectedvalue > 0 && $preselectedvalue == $obj->rowid)
						{
                                                    if($obj->ig)
                                                    {
							$out.= '<option value="'.$obj->rowid.'" selected>'.$obj->ig."-".$label.'</option>';
                                                    }
                                                    else
                                                    {
                                                        $out.= '<option value="'.$obj->rowid.'" selected>'.$label.'</option>';
                                                    }
						}
						else
						{
                                                    if($obj->ig)
                                                    {
							$out.= '<option value="'.$obj->rowid.'">'.$obj->ig."-".$label.'</option>';
                                                    }
                                                    else
                                                    {
                                                    
							$out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
                                                    }
						}
					}
					else
					{
						array_push($outarray, array('key'=>$obj->rowid, 'value'=>$label, 'label'=>$label));
					}

					$i++;
					if (($i % 10) == 0) $out.="\n";
				}
			}

			$out.= '</select>'."\n";
		}
		else
		{
			dol_print_error($this->db);
		}

		$this->result=array('nbofelement'=>$num);

		if ($outputmode) return $outarray;
		return $out;
                    
            
            }
                 
                
		
	}

}