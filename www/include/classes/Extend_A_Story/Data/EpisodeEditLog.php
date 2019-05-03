<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2019 Jeffrey J. Weston <jjweston@gmail.com>


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

class EpisodeEditLog
{
    public static function createTable()
    {
        $dbConnection = Util::getDbConnection();

        $dbStatement = $dbConnection->prepare( "DROP TABLE IF EXISTS EpisodeEditLog" );
        $dbStatement->execute();

        $sql =
<<<SQL
            CREATE TABLE EpisodeEditLog
            (
                EpisodeEditLogID  INT UNSIGNED    NOT NULL  PRIMARY KEY  AUTO_INCREMENT,
                EpisodeID         INT UNSIGNED    NOT NULL,
                SchemeID          INT UNSIGNED    NOT NULL,
                ImageID           INT UNSIGNED    NOT NULL,
                IsLinkable        CHAR   ( 1   )  NOT NULL,
                IsExtendable      CHAR   ( 1   )  NOT NULL,
                AuthorMailto      CHAR   ( 1   )  NOT NULL,
                AuthorNotify      CHAR   ( 1   )  NOT NULL,
                Title             VARCHAR( 255 )  NOT NULL,
                Text              TEXT            NOT NULL,
                AuthorName        VARCHAR( 255 )  NOT NULL,
                AuthorEmail       VARCHAR( 255 )  NOT NULL,
                EditDate          VARCHAR( 255 )  NOT NULL,
                EditLogEntry      VARCHAR( 255 )  NOT NULL,
                INDEX( EpisodeID )
            ) DEFAULT CHARSET=latin1;
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
    }
}

?>
