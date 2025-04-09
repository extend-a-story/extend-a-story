<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2025 Jeffrey J. Weston <jjweston@gmail.com>


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

abstract class Version
{
    public static function getVersion()
    {
        $databaseVersion = Database::getDatabaseVersion();
        switch ( $databaseVersion )
        {
            case 1 : return new Version1();
            case 2 : return new Version2();
            case 3 : return new Version3();
            case 4 : return new Version4();
            case 5 : return new Version5();
            default : throw new StoryException( "Unrecognized database version." );
        }
    }

    public abstract function getDatabaseVersion();
    public abstract function getStoryVersion();
    public abstract function getAddedTableNames();
    public abstract function checkDatabase();
    public abstract function upgradeDatabase( $upgradeData );
}
