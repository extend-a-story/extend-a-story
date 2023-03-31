<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2023 Jeffrey J. Weston <jjweston@gmail.com>


This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


For information about Extend-A-Story and its authors, please visit the website:
http://www.sir-toby.com/extend-a-story/

*/

namespace Extend_A_Story\Data;

use \Extend_A_Story\Util;

class Session
{
    public static function createTable()
    {
        $dbConnection = Util::getDbConnection();

        $dbStatement = $dbConnection->prepare( "DROP TABLE IF EXISTS Session" );
        $dbStatement->execute();

        $sql =
<<<SQL
            CREATE TABLE Session
            (
                SessionID   INT UNSIGNED  NOT NULL  PRIMARY KEY  AUTO_INCREMENT,
                UserID      INT UNSIGNED  NOT NULL,
                SessionKey  INT UNSIGNED  NOT NULL,
                AccessDate  DATETIME      NOT NULL
            ) DEFAULT CHARSET=latin1
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
    }
}

?>
