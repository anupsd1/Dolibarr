-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see http://www.gnu.org/licenses/.


CREATE TABLE llx_miscellaneous_dailywork(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL, 
	entity integer DEFAULT 1 NOT NULL, 
	description text, 
	date_creation datetime, 
	tms timestamp NOT NULL, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14), 
	prodlotiii integer, 
	leniv double, 
	breadthiv double, 
	ariv double, 
	total double, 
	curuser varchar(128), 
	usergr varchar(128), 
	newusrss varchar(128), 
	newuara varchar(128), 
	camno integer, 
	survey integer, 
	dos integer, 
	densi integer, 
	gamma integer, 
	prodlot integer, 
	leni double, 
	breadthi double, 
	ari double, 
	prodloti integer, 
	lenii double, 
	expi double, 
	breadthii double, 
	expii double, 
	arii double, 
	leniii double, 
	expiv double, 
	breadthiii double, 
	expiii double, 
	ariii double, 
	prodlotii integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;