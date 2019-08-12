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


CREATE TABLE llx_miscellaneous_safetyeqttest(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL, 
	date_creation datetime NOT NULL, 
	tms timestamp NOT NULL, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14), 
	curuser varchar(128), 
	usergr varchar(128), 
	survey integer, 
	rsmwork integer, 
	survey2 integer, 
	rsmwork2 integer, 
	survey3 integer, 
	rsmwork3 integer, 
	dos integer, 
	doswork integer, 
	dos2 integer, 
	doswork2 integer, 
	dos3 integer, 
	doswork3 integer, 
	leadpot integer, 
	cvtong integer, 
	dangelboards integer, 
	cardon integer, 
	sirens integer, 
	camlock integer, 
	camwork integer, 
	camspecify varchar(128), 
	guidetube integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;