<?php

 include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';

class MyUsers extends User
{
        var $db;
        
        public function __construct($db) {
            parent::__construct($db);
        }
        /**
         * DEFINED BY ANUP
         */
        function getGroup($usrid)
        {
            
            global $conf, $langs, $user;
                
                //$user->group = $group;
                
		$error=0;

		$this->db->begin();
                
                /*
                $sql = "SELECT nom FROM ".MAIN_DB_PREFIX."usergroup_user ";
                $sql.="WHERE ".$usrid."=".$fk_usergroup." ";
                */
                $sql = "SELECT g.rowid, ug.entity, g.nom as usergroup_entity";
		$sql.= " FROM ".MAIN_DB_PREFIX."usergroup as g,";
		$sql.= " ".MAIN_DB_PREFIX."usergroup_user as ug";
		$sql.= " WHERE ug.fk_usergroup = g.rowid";
		$sql.= " AND ug.fk_user = ".$usrid;
		
                $ret = array();
                
                
                //$result = $this->db->query($sql);
		$result = $this->db->query($sql);
		if ($result)
		{
			//if ($this->db->num_rows($result))
                        //while ($obj = $this->db->fetch_object($result))			
			while($obj = $this->db->fetch_object($result))
                        {
                            //$obj = $this->db->fetch_object($result);
                            if (! array_key_exists($obj->rowid, $ret))
                            {
                                
                                    include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
                                    $newgroup=new UserGroup($this->db);
                                    $newgroup->fetch($obj->rowid, '', $load_members);
                                    $ret[$obj->rowid]=$newgroup;
                                    $this->newgroupid = (string)$obj->usergroup_nom;
                                   
                            }
                             else {
                                    return 2;
                                }
                     
                        }
                            
                            $this->db->free($result);
                            //if($this->newgroupid)
                              //  return $this->newgroupid;
                            //else
                              //  return 1;
                            //return $ret[$obj->rowid];
                            return $newgroupid;
                            //return $obj->name; 
                            //newgroupid;
                        //return 1;
		}
		else
		{
			$this->error=$this->db->lasterror();
			return -1;
		}
            
        }
        
        /*
         * DEFINED BY ANUP:
         * 
         */
        function getDesignation($usrid)
        {
            global $conf, $langs, $user;
            $sql = "SELECT u.rowid, u.job";
            $sql.= " FROM ".MAIN_DB_PREFIX."user as u";
            $sql.= " WHERE u.rowid=".$usrid;
            $result = $this->db->query($sql);
            $obj = $this->db->fetch_object($result);
            $myvar = $obj->job;
            return $myvar;
        }
        
        function getGroups($userid)
        {
            /*include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
            $newgroup=new UserGroup($this->db);
            $res = $newgroup->listGroupsForUser($usrid);
            return $res;
              */
            	global $conf, $user;

		$ret=array();

		/*This is the original
                 * $sql = "SELECT g.rowid, ug.entity as usergroup_entity";
                 * 
                 */
                $sql = "SELECT g.rowid, g.nom, ug.entity as usergroup_entity";
		
		$sql.= " FROM ".MAIN_DB_PREFIX."usergroup as g,";
		$sql.= " ".MAIN_DB_PREFIX."usergroup_user as ug";
		$sql.= " WHERE ug.fk_usergroup = g.rowid";
		$sql.= " AND ug.fk_user = ".$userid;
		if(! empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && ! $user->entity)
		{
			$sql.= " AND g.entity IS NOT NULL";
		}
		else
		{
			$sql.= " AND g.entity IN (0,".$conf->entity.")";
		}
		$sql.= " ORDER BY g.nom";

		dol_syslog(get_class($this)."::listGroupsForUser", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			while ($obj = $this->db->fetch_object($result))
			{
				if (! array_key_exists($obj->rowid, $ret))
				{
                                        include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
					$newgroup=new UserGroup($this->db);
					$newgroup->fetch($obj->rowid, '', $load_members);
					$ret[$obj->rowid]=$newgroup;
				}

				/*This is the original-
                                 * $ret[$obj->rowid]->usergroup_entity[]=$obj->usergroup_entity;
                                 * 
                                 */
                                $ret = $obj->nom;
                                
			}

			$this->db->free($result);

			return $ret;
                }       
	
        }
        
        /*
         * DEFINED BY ANUP
        */
         //BY ANUP
        function listUsersForGroups($groupname, $excludefilter='', $mode=0)
	{
		global $conf, $user;
                $sqls = "SELECT g.rowid, g.entity, g.nom as name, g.note, g.datec, g.tms as datem";
		$sqls.= " FROM ".MAIN_DB_PREFIX."usergroup as g";
		if ($groupname)
		{
			$sqls.= " WHERE g.nom = '".$this->db->escape($groupname)."'";
		}
                
                $results = $this->db->query($sqls);
		if ($results)
		{
                    $objs = $this->db->fetch_object($results);
                    $mygroupid = $objs->rowid;
                }
                //$this->db->free($results);
		$ret=array();

		$sql = "SELECT u.rowid";
		$sql.= ", ug.entity as usergroup_entity";
		$sql.= " FROM ".MAIN_DB_PREFIX."user as u";
		$sql.= ", ".MAIN_DB_PREFIX."usergroup_user as ug";
		$sql.= " WHERE 1 = 1";
		$sql.= " AND ug.fk_user = u.rowid";
		$sql.= " AND ug.fk_usergroup = ".$mygroupid;
		
		if (! empty($excludefilter)) $sql.=' AND ('.$excludefilter.')';

		//dol_syslog(get_class($this)."::listUsersForGroup", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			while ($obj = $this->db->fetch_object($resql))
			{
				if (! array_key_exists($obj->rowid, $ret))
				{
					if ($mode != 1)
					{
						$newuser=new User($this->db);
						$newuser->fetch($obj->rowid);
						$ret[$obj->rowid]=$newuser;
					}
					else $ret[$obj->rowid]=$obj->rowid;
				}
				
			}

			$this->db->free($resql);

			return $ret;
		}
		else
		{
			$this->error=$this->db->lasterror();
			return -1;
		}
	}
       
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

