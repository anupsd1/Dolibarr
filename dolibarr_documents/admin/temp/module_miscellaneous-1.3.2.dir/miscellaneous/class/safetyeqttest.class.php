<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 */

/**
 * \file        class/safetyeqttest.class.php
 * \ingroup     miscellaneous
 * \brief       This file is a CRUD class file for SafetyEqtTest (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
//require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once 'myactions_fetchobject.inc.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for SafetyEqtTest
 */
class SafetyEqtTest extends MyActionsObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'safetyeqttest';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'miscellaneous_safetyeqttest';
	/**
	 * @var int  Does safetyeqttest support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;
	/**
	 * @var int  Does safetyeqttest support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;
	/**
	 * @var string String with name of icon for safetyeqttest. Must be the part after the 'object_' into object_safetyeqttest.png
	 */
	public $picto = 'safetyeqttest@miscellaneous';


	/**
	 *  'type' if the field format.
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed.
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only. Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'default' is a default value for creation (can still be replaced by the global setup of default values)
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'position' is the sort order of field.
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' is the CSS style to use on field. For example: 'maxwidth200'
	 *  'help' is a string visible as a tooltip on field
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-1, 'position'=>1, 'notnull'=>1, 'index'=>1, 'comment'=>"Id",),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>1, 'visible'=>1, 'position'=>10, 'notnull'=>1, 'index'=>1, 'searchall'=>1, 'comment'=>"Reference of object", 'showoncombobox'=>'1',),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'position'=>500, 'notnull'=>1,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'position'=>501, 'notnull'=>1,),
		'fk_user_creat' => array('type'=>'integer', 'label'=>'UserAuthor', 'enabled'=>1, 'visible'=>-2, 'position'=>510, 'notnull'=>1, 'foreignkey'=>'llx_user.rowid',),
		'fk_user_modif' => array('type'=>'integer', 'label'=>'UserModif', 'enabled'=>1, 'visible'=>-2, 'position'=>511, 'notnull'=>-1,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'visible'=>-2, 'position'=>1000, 'notnull'=>-1,),
		'curuser' => array('type'=>'varchar(128)', 'label'=>'RSO', 'enabled'=>1, 'visible'=>1, 'position'=>5, 'notnull'=>-1,),
		'usergr' => array('type'=>'varchar(128)', 'label'=>'Site', 'enabled'=>1, 'visible'=>1, 'position'=>7, 'notnull'=>-1,),
		'survey' => array('type'=>'integer:SurveyMeter:custom/miscellaneous/class/surveymeter.class.php', 'label'=>'Radiation Survey Meter', 'enabled'=>1, 'visible'=>1, 'position'=>12, 'notnull'=>-1,),
		'rsmwork' => array('type'=>'integer', 'label'=>'RSMWORK', 'enabled'=>1, 'visible'=>1, 'position'=>14, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'survey2' => array('type'=>'integer:SurveyMeter:custom/miscellaneous/class/surveymeter.class.php', 'label'=>'Radiation Survey Meter 2', 'enabled'=>1, 'visible'=>1, 'position'=>16, 'notnull'=>-1,),
		'rsmwork2' => array('type'=>'integer', 'label'=>'RSMWORK2', 'enabled'=>1, 'visible'=>1, 'position'=>18, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'survey3' => array('type'=>'integer:SurveyMeter:custom/miscellaneous/class/surveymeter.class.php', 'label'=>'Radiation Survey Meter 3', 'enabled'=>1, 'visible'=>1, 'position'=>20, 'notnull'=>-1,),
		'rsmwork3' => array('type'=>'integer', 'label'=>'RSMWORK3', 'enabled'=>1, 'visible'=>1, 'position'=>22, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'dos' => array('type'=>'integer:Dosimeter:custom/miscellaneous/class/dosimeter.class.php', 'label'=>'Pocket Dosimeter', 'enabled'=>1, 'visible'=>1, 'position'=>24, 'notnull'=>-1,),
		'doswork' => array('type'=>'integer', 'label'=>'DOSWORK', 'enabled'=>1, 'visible'=>1, 'position'=>26, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'dos2' => array('type'=>'integer:Dosimeter:custom/miscellaneous/class/dosimeter.class.php', 'label'=>'Pocket Dosimeter 2', 'enabled'=>1, 'visible'=>1, 'position'=>28, 'notnull'=>-1,),
		'doswork2' => array('type'=>'integer', 'label'=>'DOSWORK2', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'dos3' => array('type'=>'integer:Dosimeter:custom/miscellaneous/class/dosimeter.class.php', 'label'=>'Pocket Dosimeter 3', 'enabled'=>1, 'visible'=>1, 'position'=>32, 'notnull'=>-1,),
		'doswork3' => array('type'=>'integer', 'label'=>'DOSWORK3', 'enabled'=>1, 'visible'=>1, 'position'=>34, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'leadpot' => array('type'=>'integer', 'label'=>'Lead Pot', 'enabled'=>1, 'visible'=>1, 'position'=>38, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'cvtong' => array('type'=>'integer', 'label'=>'C V Tong', 'enabled'=>1, 'visible'=>1, 'position'=>40, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'dangelboards' => array('type'=>'integer', 'label'=>'Dangel Boards', 'enabled'=>1, 'visible'=>1, 'position'=>42, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'cardon' => array('type'=>'integer', 'label'=>'Cardon off Rope', 'enabled'=>1, 'visible'=>1, 'position'=>44, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'sirens' => array('type'=>'integer', 'label'=>'Sirens', 'enabled'=>1, 'visible'=>1, 'position'=>46, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'camlock' => array('type'=>'integer', 'label'=>'Camera Lock & Key At Place', 'enabled'=>1, 'visible'=>1, 'position'=>48, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'camwork' => array('type'=>'integer', 'label'=>'Camera Working Properly', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
		'camspecify' => array('type'=>'varchar(128)', 'label'=>'If no specify', 'enabled'=>1, 'visible'=>1, 'position'=>52, 'notnull'=>-1,),
		'guidetube' => array('type'=>'integer', 'label'=>'Guide Tube Condition Checked', 'enabled'=>1, 'visible'=>1, 'position'=>54, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'Yes', '2'=>'No')),
	);
	public $rowid;
	public $ref;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $curuser;
	public $usergr;
	public $survey;
	public $rsmwork;
	public $survey2;
	public $rsmwork2;
	public $survey3;
	public $rsmwork3;
	public $dos;
	public $doswork;
	public $dos2;
	public $doswork2;
	public $dos3;
	public $doswork3;
	public $leadpot;
	public $cvtong;
	public $dangelboards;
	public $cardon;
	public $sirens;
	public $camlock;
	public $camwork;
	public $camspecify;
	public $guidetube;
	// END MODULEBUILDER PROPERTIES



	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'safetyeqttestdet';
	/**
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	//public $fk_element = 'fk_safetyeqttest';
	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'SafetyEqtTestline';
	/**
	 * @var array  Array of child tables (child tables to delete before deleting a record)
	 */
	//protected $childtables=array('safetyeqttestdet');
	/**
	 * @var SafetyEqtTestLine[]     Array of subtable lines
	 */
	//public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $user;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible']=0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled']=0;

		// Unset fields that are disabled
		foreach($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone and object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $hookmanager, $langs;
	    $error = 0;

	    dol_syslog(__METHOD__, LOG_DEBUG);

	    $object = new self($this->db);

	    $this->db->begin();

	    // Load source object
	    $object->fetchCommon($fromid);
	    // Reset some properties
	    unset($object->id);
	    unset($object->fk_user_creat);
	    unset($object->import_key);

	    // Clear fields
	    $object->ref = "copy_of_".$object->ref;
	    $object->title = $langs->trans("CopyOf")." ".$object->title;
	    // ...

	    // Create clone
		$object->context['createfromclone'] = 'createfromclone';
	    $result = $object->createCommon($user);
	    if ($result < 0) {
	        $error++;
	        $this->error = $object->error;
	        $this->errors = $object->errors;
	    }

	    // End
	    if (!$error) {
	        $this->db->commit();
	        return $object;
	    } else {
	        $this->db->rollback();
	        return -1;
	    }
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && ! empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	/*public function fetchLines()
	{
		$this->lines=array();

		// Load lines with object SafetyEqtTestLine

		return count($this->lines)?1:0;
	}*/

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *	@param	int		$withpicto					Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *	@param	string	$option						On what the link point to ('nolink', ...)
     *  @param	int  	$notooltip					1=Disable tooltip
     *  @param  string  $morecss            		Add more css on link
     *  @param  int     $save_lastsearch_value    	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *	@return	string								String with URL
	 */
	function getNomUrl($withpicto=0, $option='', $notooltip=0, $morecss='', $save_lastsearch_value=-1)
	{
		global $db, $conf, $langs, $hookmanager;
        global $dolibarr_main_authentication, $dolibarr_main_demo;
        global $menumanager;

        if (! empty($conf->dol_no_mouse_hover)) $notooltip=1;   // Force disable tooltips

        $result = '';
        $companylink = '';

        $label = '<u>' . $langs->trans("SafetyEqtTest") . '</u>';
        $label.= '<br>';
        $label.= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;

        $url = dol_buildpath('/miscellaneous/safetyeqttest_card.php',1).'?id='.$this->id;

        if ($option != 'nolink')
        {
	        // Add param to save lastsearch_values or not
	        $add_save_lastsearch_values=($save_lastsearch_value == 1 ? 1 : 0);
	        if ($save_lastsearch_value == -1 && preg_match('/list\.php/',$_SERVER["PHP_SELF"])) $add_save_lastsearch_values=1;
	        if ($add_save_lastsearch_values) $url.='&save_lastsearch_values=1';
        }

        $linkclose='';
        if (empty($notooltip))
        {
            if (! empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
            {
                $label=$langs->trans("ShowSafetyEqtTest");
                $linkclose.=' alt="'.dol_escape_htmltag($label, 1).'"';
            }
            $linkclose.=' title="'.dol_escape_htmltag($label, 1).'"';
            $linkclose.=' class="classfortooltip'.($morecss?' '.$morecss:'').'"';

            /*
             $hookmanager->initHooks(array('safetyeqttestdao'));
             $parameters=array('id'=>$this->id);
             $reshook=$hookmanager->executeHooks('getnomurltooltip',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
             if ($reshook > 0) $linkclose = $hookmanager->resPrint;
             */
        }
        else $linkclose = ($morecss?' class="'.$morecss.'"':'');

		$linkstart = '<a href="'.$url.'"';
		$linkstart.=$linkclose.'>';
		$linkend='</a>';

		$result .= $linkstart;
		if ($withpicto) $result.=img_object(($notooltip?'':$label), ($this->picto?$this->picto:'generic'), ($notooltip?(($withpicto != 2) ? 'class="paddingright"' : ''):'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip?0:1);
		if ($withpicto != 2) $result.= $this->ref;
		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action;
		$hookmanager->initHooks(array('safetyeqttestdao'));
		$parameters=array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook=$hookmanager->executeHooks('getNomUrl',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 *  Return label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	function getLibStatut($mode=0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	function LibStatut($status, $mode=0)
	{
		if (empty($this->labelstatus))
		{
			global $langs;
			//$langs->load("miscellaneous");
			$this->labelstatus[1] = $langs->trans('Enabled');
			$this->labelstatus[0] = $langs->trans('Disabled');
		}

		if ($mode == 0)
		{
			return $this->labelstatus[$status];
		}
		if ($mode == 1)
		{
			return $this->labelstatus[$status];
		}
		if ($mode == 2)
		{
			if ($status == 1) return img_picto($this->labelstatus[$status],'statut4').' '.$this->labelstatus[$status];
			if ($status == 0) return img_picto($this->labelstatus[$status],'statut5').' '.$this->labelstatus[$status];
		}
		if ($mode == 3)
		{
			if ($status == 1) return img_picto($this->labelstatus[$status],'statut4');
			if ($status == 0) return img_picto($this->labelstatus[$status],'statut5');
		}
		if ($mode == 4)
		{
			if ($status == 1) return img_picto($this->labelstatus[$status],'statut4').' '.$this->labelstatus[$status];
			if ($status == 0) return img_picto($this->labelstatus[$status],'statut5').' '.$this->labelstatus[$status];
		}
		if ($mode == 5)
		{
			if ($status == 1) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut4');
			if ($status == 0) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut5');
		}
		if ($mode == 6)
		{
			if ($status == 1) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut4');
			if ($status == 0) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut5');
		}
	}

	/**
	 *	Charge les informations d'ordre info dans l'objet commande
	 *
	 *	@param  int		$id       Id of order
	 *	@return	void
	 */
	function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql.= ' fk_user_creat, fk_user_modif';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql.= ' WHERE t.rowid = '.$id;
		$result=$this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation   = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture)
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture   = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);

		}
		else
		{
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}


	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, paramerts come from the schedule job setup field 'Parameters'
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	//public function doScheduledJob($param1, $param2, ...)
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error='';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}
}

/**
 * Class SafetyEqtTestLine. You can also remove this and generate a CRUD class for lines objects.
 */
/*
class SafetyEqtTestLine
{
	// @var int ID
	public $id;
	// @var mixed Sample line property 1
	public $prop1;
	// @var mixed Sample line property 2
	public $prop2;
}
*/