<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2022 Jeffrey J. Weston <jjweston@gmail.com>


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

class Episode
{
    public static function createTable()
    {
        $dbConnection = Util::getDbConnection();

        $dbStatement = $dbConnection->prepare( "DROP TABLE IF EXISTS Episode" );
        $dbStatement->execute();

        $sql =
<<<SQL
            CREATE TABLE Episode
            (
                EpisodeID          INT     UNSIGNED  NOT NULL  PRIMARY KEY  AUTO_INCREMENT,
                Parent             INT     UNSIGNED  NOT NULL,
                AuthorSessionID    INT     UNSIGNED  NOT NULL,
                EditorSessionID    INT     UNSIGNED  NOT NULL,
                SchemeID           INT     UNSIGNED  NOT NULL,
                ImageID            INT     UNSIGNED  NOT NULL,
                Status             TINYINT UNSIGNED  NOT NULL,
                IsLinkable         CHAR   ( 1   )    NOT NULL,
                IsExtendable       CHAR   ( 1   )    NOT NULL,
                AuthorMailto       CHAR   ( 1   )    NOT NULL,
                AuthorNotify       CHAR   ( 1   )    NOT NULL,
                Title              VARCHAR( 255 )    NOT NULL,
                Text               TEXT              NOT NULL,
                AuthorName         VARCHAR( 255 )    NOT NULL,
                AuthorEmail        VARCHAR( 255 )    NOT NULL,
                CreationDate       VARCHAR( 255 )    NOT NULL,
                LockDate           VARCHAR( 255 )    NOT NULL,
                LockKey            INT     UNSIGNED  NOT NULL,
                CreationTimestamp  DATETIME          NULL
            ) DEFAULT CHARSET=latin1
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
    }

    public static function populateTable()
    {
        $dbConnection = Util::getDbConnection();

        $sql =
<<<SQL
            INSERT INTO Episode
                        ( Parent, AuthorSessionID, EditorSessionID, SchemeID, ImageID,
                          Status, IsLinkable, IsExtendable, AuthorMailto, AuthorNotify,
                          Title, Text, AuthorName, AuthorEmail, CreationDate,
                          LockDate, LockKey, CreationTimestamp )
                 VALUES ( 1, 0, 0, 1, 0, 0, "N", "N", "N", "N", "-", "-", "-", "-", "-", "-", 0, NULL )
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
    }
}

?>
