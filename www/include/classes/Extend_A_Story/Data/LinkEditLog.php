<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2024 Jeffrey J. Weston <jjweston@gmail.com>


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

class LinkEditLog
{
    public static function createTable()
    {
        $dbConnection = Util::getDbConnection();

        $dbStatement = $dbConnection->prepare( "DROP TABLE IF EXISTS LinkEditLog" );
        $dbStatement->execute();

        $sql =
<<<SQL
            CREATE TABLE LinkEditLog
            (
                LinkEditLogID     INT UNSIGNED    NOT NULL  PRIMARY KEY  AUTO_INCREMENT,
                EpisodeEditLogID  INT UNSIGNED    NOT NULL,
                TargetEpisodeID   INT UNSIGNED    NOT NULL,
                IsBackLink        CHAR   ( 1   )  NOT NULL,
                Description       VARCHAR( 255 )  NOT NULL,
                INDEX( EpisodeEditLogID )
            ) DEFAULT CHARSET=latin1
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
    }
}

?>
