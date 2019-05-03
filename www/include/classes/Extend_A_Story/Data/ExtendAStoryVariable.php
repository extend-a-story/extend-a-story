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

use \PDO;

use \Extend_A_Story\Util;

class ExtendAStoryVariable
{
    public static function createTable()
    {
        $dbConnection = Util::getDbConnection();

        $dbStatement = $dbConnection->prepare( "DROP TABLE IF EXISTS ExtendAStoryVariable" );
        $dbStatement->execute();

        $sql =
<<<SQL
            CREATE TABLE ExtendAStoryVariable
            (
                VariableName  VARCHAR( 255 )  NOT NULL  PRIMARY KEY,
                IntValue      INT UNSIGNED    NULL,
                StringValue   VARCHAR( 255 )  NULL
            ) DEFAULT CHARSET=latin1;
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
    }

    public static function populateTable( $settingsStoryName, $settingsSiteName,
                                          $settingsStoryHome, $settingsSiteHome,
                                          $settingsReadEpisodeUrl, $settingsAdminEmail,
                                          $settingsMaxLinks, $settingsMaxEditDays )
    {
        ExtendAStoryVariable::populateCountDate();
        ExtendAStoryVariable::populateIntValue(    "CountValue",     0                       );
        ExtendAStoryVariable::populateStringValue( "StoryName",      $settingsStoryName      );
        ExtendAStoryVariable::populateStringValue( "SiteName",       $settingsSiteName       );
        ExtendAStoryVariable::populateStringValue( "StoryHome",      $settingsStoryHome      );
        ExtendAStoryVariable::populateStringValue( "SiteHome",       $settingsSiteHome       );
        ExtendAStoryVariable::populateStringValue( "ReadEpisodeURL", $settingsReadEpisodeUrl );
        ExtendAStoryVariable::populateStringValue( "AdminEmail",     $settingsAdminEmail     );
        ExtendAStoryVariable::populateStringValue( "IsWriteable",    "N"                     );
        ExtendAStoryVariable::populateIntValue(    "MaxLinks",       $settingsMaxLinks       );
        ExtendAStoryVariable::populateIntValue(    "MaxEditDays",    $settingsMaxEditDays    );
    }

    private static function populateCountDate()
    {
        $dbConnection = Util::getDbConnection();

        $sql =
<<<SQL
            INSERT INTO ExtendAStoryVariable
                        ( VariableName, StringValue )
                 VALUES ( "CountDate", date_format( now(), '%c/%e/%Y %l:%i:%s %p' ))
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
    }

    private static function populateIntValue( $variableName, $intValue )
    {
        $dbConnection = Util::getDbConnection();

        $sql =
<<<SQL
            INSERT INTO ExtendAStoryVariable
                        ( VariableName, IntValue )
                 VALUES ( :variableName, :intValue )
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->bindParam( ":variableName", $variableName, PDO::PARAM_STR );
        $dbStatement->bindParam( ":intValue",     $intValue,     PDO::PARAM_INT );
        $dbStatement->execute();
    }

    private static function populateStringValue( $variableName, $stringValue )
    {
        $dbConnection = Util::getDbConnection();

        $sql =
<<<SQL
            INSERT INTO ExtendAStoryVariable
                        ( VariableName, StringValue )
                 VALUES ( :variableName, :stringValue )
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->bindParam( ":variableName", $variableName, PDO::PARAM_STR );
        $dbStatement->bindParam( ":stringValue",  $stringValue,  PDO::PARAM_STR );
        $dbStatement->execute();
    }
}

?>
