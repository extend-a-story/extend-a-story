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

namespace Extend_A_Story\Upgrade;

use \Extend_A_Story\Data\Database;
use \Extend_A_Story\Util;

class Version3 extends Version
{
    public function getDatabaseVersion() { return 3; }
    public function getStoryVersion() { return "2.2.x"; }

    public function getAddedTableNames()
    {
        $version4 = new Version4();
        return $version4->getAddedTableNames();
    }

    public function checkDatabase()
    {
        $versionTables = array( "ExtendAStoryVariable", "Session", "User", "Episode", "Link",
                                "EpisodeEditLog", "LinkEditLog", "Scheme", "Image" );
        $databaseTables = Database::getDatabaseTableNames();
        return empty( array_diff( $versionTables, $databaseTables ));
    }

    public function upgradeDatabase( $upgradeData )
    {
        $this->alterUserTable();
    }

    private function alterUserTable()
    {
        $dbConnection = Util::getDbConnection();
        $sql = "ALTER TABLE User MODIFY COLUMN Password  VARCHAR( 255 )  NOT NULL";
        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
    }
}
