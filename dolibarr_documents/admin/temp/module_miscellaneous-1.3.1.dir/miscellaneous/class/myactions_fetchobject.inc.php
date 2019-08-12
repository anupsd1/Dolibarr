<?php
include_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
abstract class MyActionsObject extends CommonObject
{
    
        

    /**
	 * Return HTML string to put an input field into a page
	 * Code very similar with showInputField of extra fields
	 *
	 * @param  array   		$val	       Array of properties for field to show
	 * @param  string  		$key           Key of attribute
	 * @param  string  		$value         Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value)
	 * @param  string  		$moreparam     To add more parametes on html input tag
	 * @param  string  		$keysuffix     Prefix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string  		$keyprefix     Suffix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string|int	$showsize      Value for css to define size. May also be a numeric.
	 * @return string
	 */
	function showInputField($val, $key, $value, $moreparam='', $keysuffix='', $keyprefix='', $showsize=0)
	{
                include_once('myusers.class.php');
                
		global $conf,$langs,$form;
                global $user, $usergroup;
                
                //BY ANUP
                //IMPORTANT VARIABLE USED LATER!!
                //The following works:
                //$customvalue=$user->getGroups($user->id);
                
                $myuser = new MyUsers($this->db);
                $customvalue = $myuser->getGroups($user->id);
		
                if (! is_object($form))
		{
			//require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
			//$form=new Form($this->db);
                        require_once 'myhtmlform.class.php';
                        $form = new CustomForm($this->db);
		}

		$objectid = $this->id;

		$label= $val['label'];
		$type = $val['type'];
		$size = $val['css'];

		// Convert var to be able to share same code than showInputField of extrafields
		if (preg_match('/varchar\((\d+)\)/', $type, $reg))
		{
			$type = 'varchar';		// convert varchar(xx) int varchar
			$size = $reg[1];
		}
		elseif (preg_match('/varchar/', $type)) $type = 'varchar';		// convert varchar(xx) into varchar
		elseif (preg_match('/double/', $type)) $type = 'double';		// convert double(xx) into double
		if (is_array($val['arrayofkeyval'])) $type='select';
		if (preg_match('/^integer:(.*):(.*)/i', $val['type'], $reg)) $type='link';
                //if (preg_match('/^integer:(.*):(.*):(.*)/i', $val['type'], $reg)) $type='customlink';
                //if(preg_match('/^integer:(.*):(.*):(.*):(.*)/i', $val['type'], $reg)) $type='stockcustomlink';
                //if(preg_match('/^integer:(.*):(.*):(.*):(.*):(.*):(.*)/i', $val['type'], $reg)) $type='stockcustomlink2';
                if(preg_match('/customlink/i', $val['type'], $reg)) $type='customlink';
                if(preg_match('/stockcustomlink/i', $val['type'], $reg)) $type='stockcustomlink';
                if(preg_match('/myownlink/i', $val['type'], $reg)) $type='myownlink';
                
                
		//$elementtype=$this->attribute_elementtype[$key];	// seems to not be used
		$default=$val['default'];
		$computed=$val['computed'];
		$unique=$val['unique'];
		$required=$val['required'];
		$param=$val['param'];
		if (is_array($val['arrayofkeyval'])) $param['options'] = $val['arrayofkeyval'];
                /*
                if(preg_match('/^integer:(.*):(.*):(.*):(.*):(.*):(.*)/i', $val['type'], $reg))
                {
                    $type='stockcustomlink2';
                    //$param['options']=array($reg[1].':'.$reg[2]=>$reg[1].':'.$reg[2]);
                    $param['options']=array($reg[1].':'.$reg[2].':'.$reg[3].':'.$reg[4].':'.$reg[5].':'.$reg[6]=>$reg[1].':'.$reg[2].':'.$reg[3].':'.$reg[4].':'.$reg[5].':'.$reg[6]);
                }
                elseif(preg_match('/^integer:(.*):(.*):(.*):(.*)/i', $val['type'], $reg))
                {
                    $type='stockcustomlink';
                    $param['options']=array($reg[1].':'.$reg[2].':'.$reg[3].':'.$reg[4]=>$reg[1].':'.$reg[2].':'.$reg[3].':'.$reg[4]);
                }
                elseif (preg_match('/^integer:(.*):(.*):(.*)/i', $val['type'], $reg)) 
                {
                    $type='customlink';
                    $param['options']=array($reg[1].':'.$reg[2].':'.$reg[3]=>$reg[1].':'.$reg[2].':'.$reg[3]);
                }
                 * 
                 */
                if(preg_match('/myownlink/i', $val['type'], $reg))
                {
                    $type='myownlink';
                    $param['options']=array($reg[1].':'.$reg[2]=>$reg[1].':'.$reg[2]);
                }
                elseif(preg_match('/stockcustomlink/i', $val['type'], $reg))
                {
                    $type='stockcustomlink';
                    $param['options']=array($reg[1].':'.$reg[2]=>$reg[1].':'.$reg[2]);
                }
                
                elseif(preg_match('/customlink/i', $val['type'], $reg)) $type='customlink';
                elseif (preg_match('/^integer:(.*):(.*)/i', $val['type'], $reg))
		{
			$type='link';
			$param['options']=array($reg[1].':'.$reg[2]=>$reg[1].':'.$reg[2]);
		}
		$langfile=$val['langfile'];
		$list=$val['list'];
		$hidden=(abs($val['visible'])!=1 ? 1 : 0);
		$help=$val['help'];

		if ($computed)
		{
			if (! preg_match('/^search_/', $keyprefix)) return '<span class="opacitymedium">'.$langs->trans("AutomaticallyCalculated").'</span>';
			else return '';
		}

		// Use in priorit showsize from parameters, then $val['css'] then autodefine
		if (empty($showsize) && ! empty($val['css']))
		{
			$showsize = $val['css'];
		}
		if (empty($showsize))
		{
			if ($type == 'date')
			{
				//$showsize=10;
				$showsize = 'minwidth100imp';
			}
			elseif ($type == 'datetime')
			{
				//$showsize=19;
				$showsize = 'minwidth200imp';
			}
			elseif (in_array($type,array('int','double','price')))
			{
				//$showsize=10;
				$showsize = 'maxwidth75';
			}
			elseif ($type == 'url')
			{
				$showsize='minwidth400';
			}
			elseif ($type == 'boolean')
			{
				$showsize='';
			}
			else
			{
				if (round($size) < 12)
				{
					$showsize = 'minwidth100';
				}
				else if (round($size) <= 48)
				{
					$showsize = 'minwidth200';
				}
				else
				{
					//$showsize=48;
					$showsize = 'minwidth400';
				}
			}
		}
		//var_dump($showsize.' '.$size);
		if (in_array($type,array('date','datetime')))
		{
			$tmp=explode(',',$size);
			$newsize=$tmp[0];

			$showtime = in_array($type,array('datetime')) ? 1 : 0;

			// Do not show current date when field not required (see select_date() method)
			if (!$required && $value == '') $value = '-1';

			// TODO Must also support $moreparam
			$out = $form->select_date($value, $keyprefix.$key.$keysuffix, $showtime, $showtime, $required, '', 1, ($keyprefix != 'search_' ? 1 : 0), 1, 0, 1);
		}
		elseif (in_array($type,array('int','integer')))
		{
			$tmp=explode(',',$size);
			$newsize=$tmp[0];
			$out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$newsize.'" value="'.$value.'"'.($moreparam?$moreparam:'').'>';
		}
		elseif (preg_match('/varchar/', $type))
		{
                        if($key == 'curuser')
                        {
                                include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
                            
                                global $user;
                    //$value = $user->lastname;
                    //$value = $user->fetch();
                                 
                    
                                $value= $user->lastname;
                    //$out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.$value.'"'.($moreparam?$moreparam:'').' disabled>';
                             
                                $out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.dol_escape_htmltag($value).'"'.($moreparam?$moreparam:'').' readonly>';
                        }
                        elseif($key == 'empname')
                        {
                            //$out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone"'.$keyprefix.$key.$keysuffix.' name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.dol_escape_htmltag($value).'"'.($moreparam?$moreparam:'').'>';
                            //$out='<input type="text" id="'.$keyprefix.$key.$keysuffix.'"><br><ul class="mylist"><li></li></ul>';
                            $out='<input type="text" id="'.$keyprefix.$key.$keysuffix.'"><br><div id="mydiv"></div>';
                        }
                        elseif(($key == 'usergr') )
                        {
                            include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
                    
                            global $user, $usergroup;
                            
                            //The following works:
                            //$value = $user->getGroups($user->id);
                            $myuser = new MyUsers($this->db);
                            $value = $myuser->getGroups($user->id);
                            
                            $out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.($value).'"'.($moreparam?$moreparam:'').' readonly>';
                        }
                        elseif($key == 'authdesg')
                        {
                            global $user;
                            $myuser = new MyUsers($this->db);
                            //The following works:
                            //$value = $user->getDesignation($user->id);
                            $value = $myuser->getDesignation($user->id);
                            $out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.($value).'"'.($moreparam?$moreparam:'').' readonly>';
                        }
                        elseif($key == 'newusrss')
                        {
                            include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
                            global $user;
                    			//$listofusers = new UserGroup($this->db);
                                        $listofusers = new UserGroup($this->db);
                                        $userid = $user->id;
                                        $myuser = new MyUsers($this->db);
                                        
                                        //The follwing work two lines work
                                        //$groupname = $user->getGroups($user->id);
                                        //$myarr = $user->listUsersForGroups($groupname);
                                        $groupname = $myuser->getGroups($user->id);
                                        $myarr = $myuser->listUsersForGroups($groupname);
                                        
                                        $i=0;
                                        if(is_array($myarr))
                                        {
                                            //$out = '<select>';
                                            $out='<select class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" '.($moreparam?$moreparam:'').'>';
                        
                                            //$out.='<option value=-1 selected></option>';
                                            while($i <= count($myarr))
                                            {
                                               $out.='<option value="'.$myarr[$i]->lastname.'">'.$myarr[$i]->lastname.'</option>';
                                               $i++;
                                            }
                                           
                                            $out.='</select>';
                                        }
                        }
                        elseif($key == 'newuara')
                        {
                            include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
                            global $user, $usergroup;
                    			$listofusers = new UserGroup($this->db);
                                        $listofusers->fetch($id='', $groupname=$customvalue, $loadmembers=true);
                                        $myarr=$listofusers->listUsersForGroup();
                                        //$myarr = $usergroup->listUsersForGroup();
                                        $i=0;
                                        if(is_array($myarr))
                                        {
                                           // $out = '<select>';
                                           $out.='<select class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" '.($moreparam?$moreparam:'').'>';
                        
                                            //$out.='<option value=-1 selected></option>';
                                            while($i <= count($myarr))
                                            {
                                                $out.='<option value="'.$myarr[$i]->lastname.'">'.$myarr[$i]->lastname.'</option>';
                                                $i++;
                                            }
                                           
                                            $out.='</select>';
                                        }
                    
                
                        }
                        elseif(($key=='rep') || ($key=='bio') || ($key=='desg') || ($key=='authempno'))
                        {
                            $out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.dol_escape_htmltag($value).'"'.($moreparam?$moreparam:'').' readonly>';
                        }
                        //if($key == '')
                        else
                        {
                            $out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.dol_escape_htmltag($value).'"'.($moreparam?$moreparam:'').'>';
                        }
                }
		elseif (in_array($type, array('mail', 'phone', 'url')))
		{
			$out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.$value.'" '.($moreparam?$moreparam:'').'>';
		}
		elseif ($type == 'text')
		{
			require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
			$doleditor=new DolEditor($keyprefix.$key.$keysuffix,$value,'',200,'dolibarr_notes','In',false,false,0,ROWS_5,'90%');
                        //$doleditor=new DolEditor($keyprefix.$key.$keysuffix,$keyprefix.$key.$keysuffix,'',200,'dolibarr_notes','In',false,false,0,ROWS_5,'90%');
			$out=$doleditor->Create(1);
		}
		elseif ($type == 'html')
		{
			require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
			$doleditor=new DolEditor($keyprefix.$key.$keysuffix,$value,'',200,'dolibarr_notes','In',false,false,! empty($conf->fckeditor->enabled) && $conf->global->FCKEDITOR_ENABLE_SOCIETE,ROWS_5,'90%');
			$out=$doleditor->Create(1);
		}
		elseif ($type == 'boolean')
		{
			$checked='';
			if (!empty($value)) {
				$checked=' checked value="1" ';
			} else {
				$checked=' value="1" ';
			}
			$out='<input type="checkbox" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" '.$checked.' '.($moreparam?$moreparam:'').'>';
		}
		elseif ($type == 'price')
		{
			if (!empty($value)) {		// $value in memory is a php numeric, we format it into user number format.
				$value=price($value);
			}
			$out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.$value.'" '.($moreparam?$moreparam:'').'> '.$langs->getCurrencySymbol($conf->currency);
		}
		elseif ($type == 'double')
		{
			if (!empty($value)) {		// $value in memory is a php numeric, we format it into user number format.
				$value=price($value);
			}
                        if($keyprefix.$key.$keysuffix=="leni")
                        {
                            $out='<input class="'.$keyprefix.$key.$keysuffix.' leni" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.$value.'" '.($moreparam?$moreparam:'').'> ';
                        }
                        elseif($keyprefix.$key.$keysuffix=="breadthi")
                        {
                            $out='<input class="'.$keyprefix.$key.$keysuffix.' breadthi" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.$value.'" '.($moreparam?$moreparam:'').'> ';
                        }
                        else{
			//$out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.$value.'" '.($moreparam?$moreparam:'').'> ';
                        $out='<input class="'.$keyprefix.$key.$keysuffix.' amount" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.$value.'" '.($moreparam?$moreparam:'').'> ';
                        }
                        //$out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.$keyprefix.$key.$keysuffix.'"> ';
		}
                elseif ($type == 'select')
		{
			$out = '';
			if (! empty($conf->use_javascript_ajax) && ! empty($conf->global->MAIN_EXTRAFIELDS_USE_SELECT2))
			{
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
				$out.= ajax_combobox($keyprefix.$key.$keysuffix, array(), 0);
			}

			$out.='<select class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" '.($moreparam?$moreparam:'').'>';
                        
			if ((! isset($val['default'])) || ($val['notnull'] != 1)) $out.='<option value="0">&nbsp;</option>';
			foreach ($param['options'] as $key => $val)
			{
				if ((string) $key == '') continue;
				list($val, $parent) = explode('|', $val);
				$out.='<option value="'.$key.'"';
				$out.= (((string) $value == (string) $key)?' selected':'');
				$out.= (!empty($parent)?' parent="'.$parent.'"':'');
				$out.='>'.$val.'</option>';
			}
			$out.='</select>';
		}
		elseif ($type == 'sellist')
		{
			$out = '';
			if (! empty($conf->use_javascript_ajax) && ! empty($conf->global->MAIN_EXTRAFIELDS_USE_SELECT2))
			{
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
				$out.= ajax_combobox($keyprefix.$key.$keysuffix, array(), 0);
			}

			$out.='<select class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" '.($moreparam?$moreparam:'').'>';
			if (is_array($param['options']))
			{
				$param_list=array_keys($param['options']);
				$InfoFieldList = explode(":", $param_list[0]);
				// 0 : tableName
				// 1 : label field name
				// 2 : key fields name (if differ of rowid)
				// 3 : key field parent (for dependent lists)
				// 4 : where clause filter on column or table extrafield, syntax field='value' or extra.field=value
				$keyList=(empty($InfoFieldList[2])?'rowid':$InfoFieldList[2].' as rowid');


				if (count($InfoFieldList) > 4 && ! empty($InfoFieldList[4]))
				{
					if (strpos($InfoFieldList[4], 'extra.') !== false)
					{
						$keyList='main.'.$InfoFieldList[2].' as rowid';
					} else {
						$keyList=$InfoFieldList[2].' as rowid';
					}
				}
				if (count($InfoFieldList) > 3 && ! empty($InfoFieldList[3]))
				{
					list($parentName, $parentField) = explode('|', $InfoFieldList[3]);
					$keyList.= ', '.$parentField;
				}

				$fields_label = explode('|',$InfoFieldList[1]);
				if (is_array($fields_label))
				{
					$keyList .=', ';
					$keyList .= implode(', ', $fields_label);
				}

				$sqlwhere='';
				$sql = 'SELECT '.$keyList;
				$sql.= ' FROM '.MAIN_DB_PREFIX .$InfoFieldList[0];
				if (!empty($InfoFieldList[4]))
				{
					// can use SELECT request
					if (strpos($InfoFieldList[4], '$SEL$')!==false) {
						$InfoFieldList[4]=str_replace('$SEL$','SELECT',$InfoFieldList[4]);
					}

					// current object id can be use into filter
					if (strpos($InfoFieldList[4], '$ID$')!==false && !empty($objectid)) {
						$InfoFieldList[4]=str_replace('$ID$',$objectid,$InfoFieldList[4]);
					} else {
						$InfoFieldList[4]=str_replace('$ID$','0',$InfoFieldList[4]);
					}
					//We have to join on extrafield table
					if (strpos($InfoFieldList[4], 'extra')!==false)
					{
						$sql.= ' as main, '.MAIN_DB_PREFIX .$InfoFieldList[0].'_extrafields as extra';
						$sqlwhere.= ' WHERE extra.fk_object=main.'.$InfoFieldList[2]. ' AND '.$InfoFieldList[4];
					}
					else
					{
						$sqlwhere.= ' WHERE '.$InfoFieldList[4];
					}
				}
				else
				{
					$sqlwhere.= ' WHERE 1=1';
				}
				// Some tables may have field, some other not. For the moment we disable it.
				if (in_array($InfoFieldList[0],array('tablewithentity')))
				{
					$sqlwhere.= ' AND entity = '.$conf->entity;
				}
				$sql.=$sqlwhere;
				//print $sql;

				$sql .= ' ORDER BY ' . implode(', ', $fields_label);

				dol_syslog(get_class($this).'::showInputField type=sellist', LOG_DEBUG);
				$resql = $this->db->query($sql);
				if ($resql)
				{
					$out.='<option value="0">&nbsp;</option>';
					$num = $this->db->num_rows($resql);
					$i = 0;
					while ($i < $num)
					{
						$labeltoshow='';
						$obj = $this->db->fetch_object($resql);

						// Several field into label (eq table:code|libelle:rowid)
						$fields_label = explode('|',$InfoFieldList[1]);
						if(is_array($fields_label))
						{
							$notrans = true;
							foreach ($fields_label as $field_toshow)
							{
								$labeltoshow.= $obj->$field_toshow.' ';
							}
						}
						else
						{
							$labeltoshow=$obj->{$InfoFieldList[1]};
						}
						$labeltoshow=dol_trunc($labeltoshow,45);

						if ($value==$obj->rowid)
						{
							foreach ($fields_label as $field_toshow)
							{
								$translabel=$langs->trans($obj->$field_toshow);
								if ($translabel!=$obj->$field_toshow) {
									$labeltoshow=dol_trunc($translabel,18).' ';
								}else {
									$labeltoshow=dol_trunc($obj->$field_toshow,18).' ';
								}
							}
							$out.='<option value="'.$obj->rowid.'" selected>'.$labeltoshow.'</option>';
						}
						else
						{
							if(!$notrans)
							{
								$translabel=$langs->trans($obj->{$InfoFieldList[1]});
								if ($translabel!=$obj->{$InfoFieldList[1]}) {
									$labeltoshow=dol_trunc($translabel,18);
								}
								else {
									$labeltoshow=dol_trunc($obj->{$InfoFieldList[1]},18);
								}
							}
							if (empty($labeltoshow)) $labeltoshow='(not defined)';
							if ($value==$obj->rowid)
							{
								$out.='<option value="'.$obj->rowid.'" selected>'.$labeltoshow.'</option>';
							}

							if (!empty($InfoFieldList[3]))
							{
								$parent = $parentName.':'.$obj->{$parentField};
							}

							$out.='<option value="'.$obj->rowid.'"';
							$out.= ($value==$obj->rowid?' selected':'');
							$out.= (!empty($parent)?' parent="'.$parent.'"':'');
							$out.='>'.$labeltoshow.'</option>';
						}

						$i++;
					}
					$this->db->free($resql);
				}
				else {
					print 'Error in request '.$sql.' '.$this->db->lasterror().'. Check setup of extra parameters.<br>';
				}
			}
			$out.='</select>';
		}
		elseif ($type == 'checkbox')
		{
			$value_arr=explode(',',$value);
			$out=$form->multiselectarray($keyprefix.$key.$keysuffix, (empty($param['options'])?null:$param['options']), $value_arr, '', 0, '', 0, '100%');
		}
		elseif ($type == 'radio')
		{
			$out='';
			foreach ($param['options'] as $keyopt => $val)
			{
				$out.='<input class="flat '.$showsize.'" type="radio" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" '.($moreparam?$moreparam:'');
				$out.=' value="'.$keyopt.'"';
				$out.=' id="'.$keyprefix.$key.$keysuffix.'_'.$keyopt.'"';
				$out.= ($value==$keyopt?'checked':'');
				$out.='/><label for="'.$keyprefix.$key.$keysuffix.'_'.$keyopt.'">'.$val.'</label><br>';
			}
		}
		elseif ($type == 'chkbxlst')
		{
			if (is_array($value)) {
				$value_arr = $value;
			}
			else {
				$value_arr = explode(',', $value);
			}

			if (is_array($param['options'])) {
				$param_list = array_keys($param['options']);
				$InfoFieldList = explode(":", $param_list[0]);
				// 0 : tableName
				// 1 : label field name
				// 2 : key fields name (if differ of rowid)
				// 3 : key field parent (for dependent lists)
				// 4 : where clause filter on column or table extrafield, syntax field='value' or extra.field=value
				$keyList = (empty($InfoFieldList[2]) ? 'rowid' : $InfoFieldList[2] . ' as rowid');

				if (count($InfoFieldList) > 3 && ! empty($InfoFieldList[3])) {
					list ( $parentName, $parentField ) = explode('|', $InfoFieldList[3]);
					$keyList .= ', ' . $parentField;
				}
				if (count($InfoFieldList) > 4 && ! empty($InfoFieldList[4])) {
					if (strpos($InfoFieldList[4], 'extra.') !== false) {
						$keyList = 'main.' . $InfoFieldList[2] . ' as rowid';
					} else {
						$keyList = $InfoFieldList[2] . ' as rowid';
					}
				}

				$fields_label = explode('|', $InfoFieldList[1]);
				if (is_array($fields_label)) {
					$keyList .= ', ';
					$keyList .= implode(', ', $fields_label);
				}

				$sqlwhere = '';
				$sql = 'SELECT ' . $keyList;
				$sql .= ' FROM ' . MAIN_DB_PREFIX . $InfoFieldList[0];
				if (! empty($InfoFieldList[4])) {

					// can use SELECT request
					if (strpos($InfoFieldList[4], '$SEL$')!==false) {
						$InfoFieldList[4]=str_replace('$SEL$','SELECT',$InfoFieldList[4]);
					}

					// current object id can be use into filter
					if (strpos($InfoFieldList[4], '$ID$')!==false && !empty($objectid)) {
						$InfoFieldList[4]=str_replace('$ID$',$objectid,$InfoFieldList[4]);
					} else {
						$InfoFieldList[4]=str_replace('$ID$','0',$InfoFieldList[4]);
					}

					// We have to join on extrafield table
					if (strpos($InfoFieldList[4], 'extra') !== false) {
						$sql .= ' as main, ' . MAIN_DB_PREFIX . $InfoFieldList[0] . '_extrafields as extra';
						$sqlwhere .= ' WHERE extra.fk_object=main.' . $InfoFieldList[2] . ' AND ' . $InfoFieldList[4];
					} else {
						$sqlwhere .= ' WHERE ' . $InfoFieldList[4];
					}
				} else {
					$sqlwhere .= ' WHERE 1=1';
				}
				// Some tables may have field, some other not. For the moment we disable it.
				if (in_array($InfoFieldList[0], array ('tablewithentity')))
				{
					$sqlwhere .= ' AND entity = ' . $conf->entity;
				}
				// $sql.=preg_replace('/^ AND /','',$sqlwhere);
				// print $sql;

				$sql .= $sqlwhere;
				dol_syslog(get_class($this) . '::showInputField type=chkbxlst',LOG_DEBUG);
				$resql = $this->db->query($sql);
				if ($resql) {
					$num = $this->db->num_rows($resql);
					$i = 0;

					$data=array();

					while ( $i < $num ) {
						$labeltoshow = '';
						$obj = $this->db->fetch_object($resql);

						// Several field into label (eq table:code|libelle:rowid)
						$fields_label = explode('|', $InfoFieldList[1]);
						if (is_array($fields_label)) {
							$notrans = true;
							foreach ( $fields_label as $field_toshow ) {
								$labeltoshow .= $obj->$field_toshow . ' ';
							}
						} else {
							$labeltoshow = $obj->{$InfoFieldList[1]};
						}
						$labeltoshow = dol_trunc($labeltoshow, 45);

						if (is_array($value_arr) && in_array($obj->rowid, $value_arr)) {
							foreach ( $fields_label as $field_toshow ) {
								$translabel = $langs->trans($obj->$field_toshow);
								if ($translabel != $obj->$field_toshow) {
									$labeltoshow = dol_trunc($translabel, 18) . ' ';
								} else {
									$labeltoshow = dol_trunc($obj->$field_toshow, 18) . ' ';
								}
							}

							$data[$obj->rowid]=$labeltoshow;

						} else {
							if (! $notrans) {
								$translabel = $langs->trans($obj->{$InfoFieldList[1]});
								if ($translabel != $obj->{$InfoFieldList[1]}) {
									$labeltoshow = dol_trunc($translabel, 18);
								} else {
									$labeltoshow = dol_trunc($obj->{$InfoFieldList[1]}, 18);
								}
							}
							if (empty($labeltoshow))
								$labeltoshow = '(not defined)';

								if (is_array($value_arr) && in_array($obj->rowid, $value_arr)) {
									$data[$obj->rowid]=$labeltoshow;
								}

								if (! empty($InfoFieldList[3])) {
									$parent = $parentName . ':' . $obj->{$parentField};
								}

								$data[$obj->rowid]=$labeltoshow;
						}

						$i ++;
					}
					$this->db->free($resql);

					$out=$form->multiselectarray($keyprefix.$key.$keysuffix, $data, $value_arr, '', 0, '', 0, '100%');

				} else {
					print 'Error in request ' . $sql . ' ' . $this->db->lasterror() . '. Check setup of extra parameters.<br>';
				}
			}
			$out .= '</select>';
		}
		elseif ($type == 'link')
		{
                        global $user;
                        
                        //The following works:
                        //$myvar = $user->getGroups($user->id);
			$myuser = new MyUsers($this->db);
                        $myvar = $myuser->getGroups($user->id);
                        
                        $param_list=array_keys($param['options']);				// $param_list='ObjectName:classPath'
			$showempty=(($val['notnull'] == 1 && $val['default'] != '')?0:1);
                        //if(!$myvar)
			$out=$form->selectForForms($param_list[0], $keyprefix.$key.$keysuffix, $value, $showempty, $myvar);
                        //else
                          //  $out=$form->selectForForms($param_list[0], $keyprefix.$key.$keysuffix, $value, $showempty);
		}
                
                
                //BY ANUP:
                
                elseif ($type == 'customlink')
                {
                    //$myusr = 'ABC';
                    //global $user, $usergroup;
                    //$value = $user->getGroups($user->id);
                    $value = "HELLO!";
                //$out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.$value.'"'.($moreparam?$moreparam:'').' disabled>';
                    $out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.dol_escape_htmltag($value).'"'.($moreparam?$moreparam:'').'>';
                                                                                                                                                                                    //value="'.$value.'"'.($moreparam?$moreparam:'').'>';
                }
                
                
                //BY ANUP:
                elseif($type == 'stockcustomlink')
                {   
                    //include_once DOL_DOCUMENT_ROOT.'/product/stock/class/productstockentrepot.class.php';
		
                    //$prodobj = new ProductStockEntrepot($db);
                    include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
                    global $user;
                    //$value = $user->lastname;
                    //$value = $user->fetch();
                                 
                    
                    $value= $user->lastname;
                    //$out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.$value.'"'.($moreparam?$moreparam:'').' disabled>';
                   $out='<input type="text" class="flat '.$showsize.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" maxlength="'.$size.'" value="'.dol_escape_htmltag($value).'"'.($moreparam?$moreparam:'').'>';
                }
                
                
                //BY ANUP:
                elseif($type == 'myownlink')
                {   
                    //include_once DOL_DOCUMENT_ROOT.'/product/stock/class/productstockentrepot.class.php';
		
                    //$prodobj = new ProductStockEntrepot($db);
                   include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
                    global $user;
                    			$listofusers = new UserGroup($this->db);
                                        $myarr=$listofusers->listUsersForGroup();
                                        $i=0;
                                        if(is_array($myarr))
                                        {
                                            $out = '<select>';
                                            //$out.='<option value=-1 selected></option>';
                                            while($i < count($myarr))
                                            {
                                                $out.='<option value="'.$i.'">'.$myarr[$i]->lastname.'</option>';
                                                $i++;
                                            }
                                           
                                            $out.='</select>';
                                        }
                    
                
                }
                
                
                
		elseif ($type == 'password')
		{
			// If prefix is 'search_', field is used as a filter, we use a common text field.
			$out='<input type="'.($keyprefix=='search_'?'text':'password').'" class="flat '.$showsize.'" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" value="'.$value.'" '.($moreparam?$moreparam:'').'>';
		}
		if (!empty($hidden)) {
			$out='<input type="hidden" value="'.$value.'" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'"/>';
		}
		/* Add comments
		 if ($type == 'date') $out.=' (YYYY-MM-DD)';
		 elseif ($type == 'datetime') $out.=' (YYYY-MM-DD HH:MM:SS)';
		 */
		return $out;
	}
    
}