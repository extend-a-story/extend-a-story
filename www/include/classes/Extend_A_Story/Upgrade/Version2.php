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

namespace Extend_A_Story\Upgrade;

use \Extend_A_Story\Data\Database;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class Version2 extends Version
{
    public function getDatabaseVersion() { return 2; }
    public function getStoryVersion() { return "2.1.x"; }

    public function checkDatabase()
    {
        $versionTables = array( "ExtendAStoryVariable", "Session", "User", "Episode", "Link",
                                "EpisodeEditLog", "LinkEditLog", "Scheme", "Image" );
        $databaseTables = Database::getDatabaseTableNames();
        return empty( array_diff( $versionTables, $databaseTables ));
    }

    public function upgradeDatabase( $upgradeData )
    {
        $this->alterSessionTable();
        $this->alterUserTable();
        $this->alterEpisodeTable();
        $this->alterLinkTable();
        $this->alterEpisodeEditLogTable();
        $this->alterLinkEditLogTable();
        $this->alterSchemeTable();
        $this->alterImageTable();

        $version3 = new Version3();
        $version3->upgradeDatabase( $upgradeData );
    }

    private function alterSessionTable()
    {
        $dbConnection = Util::getDbConnection();

        $sql = "ALTER TABLE Session MODIFY COLUMN SessionID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextSessionID'";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to delete NextSessionID." );
    }

    private function alterUserTable()
    {
        $dbConnection = Util::getDbConnection();

        $sql = "ALTER TABLE User MODIFY COLUMN Password  VARCHAR( 256 )  NOT NULL";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "ALTER TABLE User MODIFY COLUMN UserID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextUserID'";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to delete NextUserID." );
    }

    private function alterEpisodeTable()
    {
        $dbConnection = Util::getDbConnection();

        $sql = "ALTER TABLE Episode MODIFY COLUMN EpisodeID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextEpisodeID'";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to delete NextEpisodeID." );
    }

    private function alterLinkTable()
    {
        $dbConnection = Util::getDbConnection();

        $sql = "ALTER TABLE Link MODIFY COLUMN LinkID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextLinkID'";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to delete NextLinkID." );
    }

    private function alterEpisodeEditLogTable()
    {
        $dbConnection = Util::getDbConnection();

        $sql = "ALTER TABLE EpisodeEditLog MODIFY COLUMN EpisodeEditLogID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextEpisodeEditLogID'";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to delete NextEpisodeEditLogID." );
    }

    private function alterLinkEditLogTable()
    {
        $dbConnection = Util::getDbConnection();

        $sql = "ALTER TABLE LinkEditLog MODIFY COLUMN LinkEditLogID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextLinkEditLogID'";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to delete NextLinkEditLogID." );
    }

    private function alterSchemeTable()
    {
        $dbConnection = Util::getDbConnection();

        $sql = "ALTER TABLE Scheme MODIFY COLUMN SchemeID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextSchemeID'";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to delete NextSchemeID." );
    }

    private function alterImageTable()
    {
        $dbConnection = Util::getDbConnection();

        $sql = "ALTER TABLE Image MODIFY COLUMN ImageID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();

        $sql = "DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextImageID'";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
        if ( $dbStatement->rowCount() !== 1 ) throw new StoryException( "Failed to delete NextImageID." );
    }
}
