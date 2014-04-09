<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2014 Jeffrey J. Weston and Matthew Duhan


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

class Util
{
    public static function connectToDatabase()
    {
        global $dbHost;
        global $dbUser;
        global $dbPassword;
        global $dbDatabase;

        if ( ! mysql_connect( $dbHost, $dbUser, $dbPassword ))
        {
            throw new HardStoryException( "Unable to connect to database." );
        }

        if ( ! mysql_select_db( $dbDatabase ))
        {
            throw new HardStoryException( "Unable to select database." );
        }
    }

    public static function getSessionAndUserIDs( &$sessionID, &$userID )
    {
        // log out all users after one hour of inactivity
        $result = mysql_query( "UPDATE Session " .
                                  "SET UserID = 0 " .
                                "WHERE AccessDate < SUBDATE( NOW(), INTERVAL 1 HOUR )" );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to logout inactive users." );
        }

        $originalSessionID  = 0;
        $originalSessionKey = 0;

        if ( isset( $_COOKIE[ "sessionID" ] ))
        {
            $originalSessionID = (int) $_COOKIE[ "sessionID" ];
        }

        if ( isset( $_COOKIE[ "sessionKey" ] ))
        {
            $originalSessionKey = (int) $_COOKIE[ "sessionKey" ];
        }

        $actualSessionID  = 0;
        $actualUserID     = 0;
        $actualSessionKey = 0;

        $result = mysql_query( "SELECT UserID, SessionKey " .
                                 "FROM Session " .
                                "WHERE SessionID = " . $originalSessionID );

        if ( ! $result )
        {
            throw new HardStoryException( "Problem retrieving your session from the database." );
        }
        else
        {
            $row = mysql_fetch_row( $result );

            if ( $row )
            {
                if ( $row[ 1 ] == $originalSessionKey )
                {
                    $actualSessionID  = $originalSessionID;
                    $actualUserID     = $row[ 0 ];
                    $actualSessionKey = $originalSessionKey;

                    $result = mysql_query( "UPDATE Session " .
                                              "SET AccessDate = NOW() " .
                                            "WHERE SessionID = " . $originalSessionID );

                    if ( ! $result )
                    {
                        throw new HardStoryException(
                                "Problem updating your session in the database." );
                    }
                }
            }
        }

        if ( $actualSessionID == 0 )
        {
            // generate random session key
            $newSessionKey = mt_rand();

            // insert the session into the database
            $result = mysql_query( "INSERT " .
                                     "INTO Session " .
                                          "( " .
                                              "UserID, " .
                                              "SessionKey, " .
                                              "AccessDate " .
                                          ") " .
                                   "VALUES ".
                                          "( " .
                                              "0, " .
                                              $newSessionKey . ", " .
                                              "NOW() " .
                                          ")" );

            if ( ! $result )
            {
                throw new HardStoryException( "Unable to insert your session into the database." );
            }

            // get the new SessionID from the database
            $result = mysql_query( "SELECT LAST_INSERT_ID()" );

            if ( ! $result )
            {
                throw new HardStoryException( "Unable to query the new SessionID." );
            }

            $row = mysql_fetch_row( $result );

            if ( ! $row )
            {
                throw new HardStoryException( "Unable to fetch the new SessionID row." );
            }

            $actualSessionID  = $row[ 0 ];
            $actualSessionKey = $newSessionKey;
        }

        setcookie( "sessionID",  $actualSessionID,  time() + ( 60 * 60 * 24 * 370 ));
        setcookie( "sessionKey", $actualSessionKey, time() + ( 60 * 60 * 24 * 370 ));

        // delete all sessions over 370 days old
        $result = mysql_query( "DELETE " .
                                 "FROM Session " .
                                "WHERE AccessDate < SUBDATE( NOW(), INTERVAL 370 DAY )" );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to delete old sessions from the database." );
        }

        $sessionID = $actualSessionID;
        $userID    = $actualUserID;
    }
}

?>
