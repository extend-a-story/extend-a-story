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

namespace Extend_A_Story\Upgrade;

use \PDO;

use \Extend_A_Story\Data\Database;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class Version1 extends Version
{
    public function getDatabaseVersion() { return 1; }
    public function getStoryVersion() { return "2.0.x"; }

    public function getAddedTableNames()
    {
        $addedTables = [ "User", "EpisodeEditLog", "LinkEditLog" ];
        $version2 = new Version2();
        return array_unique( [ ...$addedTables, ...$version2->getAddedTableNames() ] );
    }

    public function checkDatabase()
    {
        $versionTables = array( "ExtendAStoryVariable", "Session", "Episode", "Link", "Scheme", "Image" );
        $databaseTables = Database::getDatabaseTableNames();
        return empty( array_diff( $versionTables, $databaseTables ));
    }

    public function upgradeDatabase( $upgradeData )
    {
        $this->createUserTable();
        $this->createEpisodeEditLogTable();
        $this->createLinkEditLogTable();
        $this->alterSessionTable();
        $this->createUser( $upgradeData );
        $this->setMaxEditDays( $upgradeData );

        $version2 = new Version2();
        $version2->upgradeDatabase( $upgradeData );
    }

    private function createUserTable()
    {
        $dbConnection = Util::getDbConnection();

        $dbStatement = $dbConnection->prepare( "DROP TABLE IF EXISTS User" );
        $dbStatement->execute();

        $sql =
<<<SQL
            CREATE TABLE User
            (
                UserID           INT     UNSIGNED  NOT NULL  PRIMARY KEY,
                PermissionLevel  TINYINT UNSIGNED  NOT NULL,
                LoginName        VARCHAR( 255 )    NOT NULL,
                Password         CHAR   ( 16  )    NOT NULL,
                UserName         VARCHAR( 255 )    NOT NULL
            ) DEFAULT CHARSET=latin1
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "INSERT INTO ExtendAStoryVariable VALUES ( 'NextUserID', 2, NULL )";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to insert NextUserID." );
    }

    private function createEpisodeEditLogTable()
    {
        $dbConnection = Util::getDbConnection();

        $dbStatement = $dbConnection->prepare( "DROP TABLE IF EXISTS EpisodeEditLog" );
        $dbStatement->execute();

        $sql =
<<<SQL
            CREATE TABLE EpisodeEditLog
            (
                EpisodeEditLogID  INT UNSIGNED    NOT NULL  PRIMARY KEY,
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
            ) DEFAULT CHARSET=latin1
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "INSERT INTO ExtendAStoryVariable VALUES ( 'NextEpisodeEditLogID', 1, NULL )";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to insert NextEpisodeEditLogID." );
    }

    private function createLinkEditLogTable()
    {
        $dbConnection = Util::getDbConnection();

        $dbStatement = $dbConnection->prepare( "DROP TABLE IF EXISTS LinkEditLog" );
        $dbStatement->execute();

        $sql =
<<<SQL
            CREATE TABLE LinkEditLog
            (
                LinkEditLogID     INT UNSIGNED    NOT NULL  PRIMARY KEY,
                EpisodeEditLogID  INT UNSIGNED    NOT NULL,
                TargetEpisodeID   INT UNSIGNED    NOT NULL,
                IsBackLink        CHAR   ( 1   )  NOT NULL,
                Description       VARCHAR( 255 )  NOT NULL,
                INDEX( EpisodeEditLogID )
            ) DEFAULT CHARSET=latin1
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "INSERT INTO ExtendAStoryVariable VALUES ( 'NextLinkEditLogID', 1, NULL )";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to insert NextLinkEditLogID." );
    }

    private function alterSessionTable()
    {
        $dbConnection = Util::getDbConnection();
        $sql = "ALTER TABLE Session ADD COLUMN UserID  INT UNSIGNED  NOT NULL  AFTER SessionID";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
    }

    private function createUser( $upgradeData )
    {
        $dbConnection = Util::getDbConnection();
        $sql = "INSERT INTO User VALUES ( 1, 4, :loginName, 'invalid', :userName )";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->bindParam( ":loginName", $upgradeData[ "adminLoginName"   ], PDO::PARAM_STR );
        $dbStatement->bindParam( ":userName",  $upgradeData[ "adminDisplayName" ], PDO::PARAM_STR );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to insert user." );
    }

    private function setMaxEditDays( $upgradeData )
    {
        $dbConnection = Util::getDbConnection();
        $sql = "INSERT INTO ExtendAStoryVariable VALUES ( 'MaxEditDays', :maxEditDays, NULL )";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->bindParam( ":maxEditDays", $upgradeData[ "settingsMaxEditDays" ], PDO::PARAM_INT );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to insert MaxEditDays." );
    }
}
