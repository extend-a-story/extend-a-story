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

namespace Extend_A_Story\Data;

use \PDO;

use \Extend_A_Story\Enum\PermissionLevel;
use \Extend_A_Story\Util;

class User
{
    const loginNameLimit     = 255;
    const loginNameThreshold = 100;
    const userNameLimit      = 255;
    const userNameThreshold  = 100;

    public static function createTable()
    {
        $dbConnection = Util::getDbConnection();

        $dbStatement = $dbConnection->prepare( "DROP TABLE IF EXISTS User" );
        $dbStatement->execute();

        $sql =
<<<SQL
            CREATE TABLE User
            (
                UserID           INT     UNSIGNED  NOT NULL  PRIMARY KEY  AUTO_INCREMENT,
                PermissionLevel  TINYINT UNSIGNED  NOT NULL,
                LoginName        VARCHAR( 255 )    NOT NULL,
                Password         VARCHAR( 255 )    NOT NULL,
                UserName         VARCHAR( 255 )    NOT NULL
            ) DEFAULT CHARSET=latin1
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->execute();
    }

    public static function populateTable( $adminLoginName, $adminDisplayName, $adminPassword )
    {
        $user = new User( PermissionLevel::Administrator, $adminLoginName, $adminPassword, $adminDisplayName );
        $user->create();
    }

    private $id;
    private $permissionLevel;
    private $loginName;
    private $password;
    private $userName;

    public function __construct( $permissionLevel, $loginName, $password, $userName )
    {
        $this->permissionLevel = $permissionLevel;
        $this->loginName       = $loginName;
        $this->password        = $password;
        $this->userName        = $userName;
    }

    public function create()
    {
        $dbConnection = Util::getDbConnection();

        $sql =
<<<SQL
            INSERT INTO User
                        ( PermissionLevel, LoginName, Password, UserName )
                 VALUES ( :permissionLevel, :loginName, SHA2( :password, 256 ), :userName )
SQL;

        $dbStatement = $dbConnection->prepare( $sql );
        $dbStatement->bindParam( ":permissionLevel", $this->permissionLevel, PDO::PARAM_INT );
        $dbStatement->bindParam( ":loginName",       $this->loginName,       PDO::PARAM_STR );
        $dbStatement->bindParam( ":password",        $this->password,        PDO::PARAM_STR );
        $dbStatement->bindParam( ":userName",        $this->userName,        PDO::PARAM_STR );
        $dbStatement->execute();
        $this->id = $dbConnection->lastInsertId();
    }
}

?>
