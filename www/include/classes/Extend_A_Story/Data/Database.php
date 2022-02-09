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

use \PDO;

use \Extend_A_Story\Util;

class Database
{
    private static $storyTableNames = array( "ExtendAStoryVariable",
                                             "Session",
                                             "User",
                                             "Episode",
                                             "Link",
                                             "EpisodeEditLog",
                                             "LinkEditLog",
                                             "Scheme",
                                             "Image" );

    public static function getConflictingTableNames()
    {
        $conflictingTableNames = array();
        $databaseTableNames = Database::getDatabaseTableNames();

        foreach ( $databaseTableNames as $databaseTableName )
        {
            if ( in_array( $databaseTableName, Database::$storyTableNames, true ))
            {
                $conflictingTableNames[] = $databaseTableName;
            }
        }

        return $conflictingTableNames;
    }

    public static function createDatabase()
    {
        ExtendAStoryVariable::createTable();
        Session::createTable();
        User::createTable();
        Episode::createTable();
        Link::createTable();
        EpisodeEditLog::createTable();
        LinkEditLog::createTable();
        Scheme::createTable();
        Image::createTable();
    }

    public static function populateDatabase( $settingsStoryName, $settingsSiteName,
                                             $settingsStoryHome, $settingsSiteHome,
                                             $settingsReadEpisodeUrl, $settingsAdminEmail,
                                             $settingsMaxLinks, $settingsMaxEditDays,
                                             $adminLoginName, $adminDisplayName, $adminPassword )
    {
        ExtendAStoryVariable::populateTable( $settingsStoryName, $settingsSiteName,
                                             $settingsStoryHome, $settingsSiteHome,
                                             $settingsReadEpisodeUrl, $settingsAdminEmail,
                                             $settingsMaxLinks, $settingsMaxEditDays );
        User::populateTable( $adminLoginName, $adminDisplayName, $adminPassword );
        Episode::populateTable();
        Scheme::populateTable();
    }

    public static function getDatabaseVersion()
    {
        // check for the User table; assume version 1 if the User table is not present
        $databaseTableNames = Database::getDatabaseTableNames();
        if ( !in_array( "User", $databaseTableNames, true )) return 1;

        // check type of Password column in User table; assume version 2 if Password column type is not varchar
        $dbStatement = Util::getDbConnection()->prepare( "DESC User Password" );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );
        $passwordColumn = $row[ 1 ];
        if ( substr( $passwordColumn, 0, 7 ) !== "varchar" ) return 2;

        // check length of Password column in User table; assume version 3 if Password column length is not 255
        if ( $passwordColumn !== "varchar(255)" ) return 3;

        // otherwise, assume version 4
        return 4;
    }

    public static function getDatabaseTableNames()
    {
        $dbConnection = Util::getDbConnection();
        $dbStatement = $dbConnection->prepare( "SHOW TABLES" );
        $dbStatement->execute();
        $tables = $dbStatement->fetchAll( PDO::FETCH_NUM );
        $tableNames = array();
        foreach ( $tables as $table ) $tableNames[] = $table[ 0 ];
        return $tableNames;
    }
}

?>
