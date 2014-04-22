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
    public static function prepareParam( &$param )
    {
        if ( get_magic_quotes_gpc() == 1 )
        {
            $param = stripslashes( $param );
        }

        $param = trim( $param );
    }

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

    public static function getStringValue( $variableName )
    {
        $result = mysql_query(
                "SELECT StringValue " .
                  "FROM ExtendAStoryVariable " .
                 "WHERE VariableName = '" . mysql_escape_string( $variableName ) . "'" );

        if ( ! $result )
        {
            throw new HardStoryException(
                    "Problem retrieving the " . $variableName . " value from the database." );
        }

        $row = mysql_fetch_row( $result );

        if ( ! $row )
        {
            throw new HardStoryException(
                    "Problem fetching " . $variableName . " row from the database." );
        }

        return $row[ 0 ];
    }

    public static function getIntValue( $variableName )
    {
        return Util::getIntValueInternal( $variableName, false );
    }

    public static function getAndIncrementIntValue( $variableName )
    {
        return Util::getIntValueInternal( $variableName, true );
    }

    function getIntValueInternal( $variableName, $increment )
    {
        if ( $increment )
        {
            // increment the value
            $result = mysql_query(
                    "UPDATE ExtendAStoryVariable " .
                       "SET IntValue = IntValue + 1 " .
                     "WHERE VariableName = '" . mysql_escape_string( $variableName ) . "'" );

            if ( ! $result )
            {
                throw new HardStoryException(
                        "Unable to increment the " . $variableName . " value in the database." );
            }
        }

        $result = mysql_query(
                "SELECT IntValue " .
                  "FROM ExtendAStoryVariable " .
                 "WHERE VariableName = '" . mysql_escape_string( $variableName ) . "'" );

        if ( ! $result )
        {
            throw new HardStoryException(
                    "Problem retrieving the " . $variableName . " value from the database." );
        }

        $row = mysql_fetch_row( $result );

        if ( ! $row )
        {
            throw new HardStoryException(
                    "Problem fetching " . $variableName . " row from the database." );
        }

        return $row[ 0 ];
    }

    public static function setStringValue( $variableName, $variableValue )
    {
        $result = mysql_query(
                "UPDATE ExtendAStoryVariable " .
                   "SET StringValue = '" . mysql_escape_string( $variableValue ) . "' " .
                 "WHERE VariableName = '" . mysql_escape_string( $variableName ) . "'" );

        if ( ! $result )
        {
            throw new HardStoryException(
                    "Problem setting the " . $variableName . " value in the database." );
        }
    }

    public static function setIntValue( $variableName, $variableValue )
    {
        $result = mysql_query(
                "UPDATE ExtendAStoryVariable " .
                   "SET IntValue = " . $variableValue . " " .
                 "WHERE VariableName = '" . mysql_escape_string( $variableName ) . "'" );

        if ( ! $result )
        {
            throw new HardStoryException(
                    "Problem setting the " . $variableName . " value in the database." );
        }
    }

    public static function createUser( $permissionLevel, $loginName, $password, $userName )
    {
        // insert the user into the database
        $result = mysql_query(
                "INSERT " .
                  "INTO User " .
                       "( " .
                           "PermissionLevel, " .
                           "LoginName, " .
                           "Password, " .
                           "UserName " .
                       ") " .
                "VALUES " .
                       "( " .
                                           $permissionLevel                  .    ", " .
                                     "'" . mysql_escape_string( $loginName ) .   "', " .
                           "PASSWORD( '" . mysql_escape_string( $password  ) . "' ), " .
                                     "'" . mysql_escape_string( $userName  ) .    "' " .
                       ")" );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to insert the user into the database." );
        }

        // get the new UserID from the database
        $result = mysql_query( "SELECT LAST_INSERT_ID()" );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to query the new UserID." );
        }

        $row = mysql_fetch_row( $result );

        if ( ! $row )
        {
            throw new HardStoryException( "Unable to fetch the new UserID row." );
        }

        return $row[ 0 ];
    }

    public static function createEpisode( $parent, $scheme )
    {
        // insert the episode into the database
        $result = mysql_query( "INSERT " .
                                 "INTO Episode " .
                                      "( " .
                                          "Parent, " .
                                          "AuthorSessionID, " .
                                          "EditorSessionID, " .
                                          "SchemeID, " .
                                          "ImageID, " .
                                          "Status, " .
                                          "IsLinkable, " .
                                          "IsExtendable, " .
                                          "AuthorMailto, " .
                                          "AuthorNotify, " .
                                          "Title, " .
                                          "Text, " .
                                          "AuthorName, " .
                                          "AuthorEmail, " .
                                          "CreationDate, " .
                                          "LockDate, " .
                                          "LockKey, " .
                                          "CreationTimestamp " .
                                      ") " .
                               "VALUES " .
                                      "( " .
                                          $parent        . ", " .
                                          "0"            . ", " .
                                          "0"            . ", " .
                                          $scheme        . ", " .
                                          "0"            . ", " .
                                          "0"            . ", " .
                                          "'N'"          . ", " .
                                          "'N'"          . ", " .
                                          "'N'"          . ", " .
                                          "'N'"          . ", " .
                                          "'-'"          . ", " .
                                          "'-'"          . ", " .
                                          "'-'"          . ", " .
                                          "'-'"          . ", " .
                                          "'-'"          . ", " .
                                          "'-'"          . ", " .
                                          "0"            . ", " .
                                          "null"         .  " " .
                                      ")" );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to insert the episode into the database." );
        }

        // get the new EpisodeID from the database
        $result = mysql_query( "SELECT LAST_INSERT_ID()" );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to query the new EpisodeID." );
        }

        $row = mysql_fetch_row( $result );

        if ( ! $row )
        {
            throw new HardStoryException( "Unable to fetch the new EpisodeID row." );
        }

        return $row[ 0 ];
    }

    public static function createLink( $sourceEpisode, $targetEpisode, $description, $isBackLink )
    {
        $description = mysql_escape_string( $description );

        // insert the link into the database
        $result = mysql_query( "INSERT " .
                                 "INTO Link " .
                                      "( " .
                                          "SourceEpisodeID, " .
                                          "TargetEpisodeID, " .
                                          "IsCreated, " .
                                          "IsBackLink, " .
                                          "Description " .
                                      ") " .
                               "VALUES " .
                                      "( " .
                                                  $sourceEpisode            .  ", " .
                                                  $targetEpisode            .  ", " .
                                          "'" . ( $isBackLink ? "Y" : "N" ) . "', " .
                                          "'" . ( $isBackLink ? "Y" : "N" ) . "', " .
                                          "'" .   $description              . "' "  .
                                      ")" );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to insert the link into the database." );
        }
    }

    public static function createEpisodeEditLog( $episode, $editLogEntry )
    {
        // read the episode to log from the database
        $result = mysql_query( "SELECT SchemeID, ImageID, IsLinkable, IsExtendable, " .
                                      "AuthorMailto, AuthorNotify, Title, Text, AuthorName, " .
                                      "AuthorEmail " .
                                 "FROM Episode WHERE EpisodeID = " . $episode );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to query original episode from database." );
        }

        $row = mysql_fetch_row( $result );

        if ( ! $row )
        {
            throw new HardStoryException( "Unable to fetch original episode row from database." );
        }

        $schemeID     = $row[ 0 ];
        $imageID      = $row[ 1 ];
        $isLinkable   = $row[ 2 ];
        $isExtendable = $row[ 3 ];
        $authorMailto = $row[ 4 ];
        $authorNotify = $row[ 5 ];
        $title        = $row[ 6 ];
        $text         = $row[ 7 ];
        $authorName   = $row[ 8 ];
        $authorEmail  = $row[ 9 ];

        // insert the episode edit log into the database
        $result = mysql_query( "INSERT " .
                                 "INTO EpisodeEditLog " .
                                      "( " .
                                          "EpisodeID, " .
                                          "SchemeID, " .
                                          "ImageID, " .
                                          "IsLinkable, " .
                                          "IsExtendable, " .
                                          "AuthorMailto, " .
                                          "AuthorNotify, " .
                                          "Title, " .
                                          "Text, " .
                                          "AuthorName, " .
                                          "AuthorEmail, " .
                                          "EditDate, " .
                                          "EditLogEntry " .
                                      ") " .
                               "VALUES " .
                                      "( " .
                                                $episode                             .  ", " .
                                                $schemeID                            .  ", " .
                                                $imageID                             .  ", " .
                                          "'" . $isLinkable                          . "', " .
                                          "'" . $isExtendable                        . "', " .
                                          "'" . $authorMailto                        . "', " .
                                          "'" . $authorNotify                        . "', " .
                                          "'" . mysql_escape_string( $title        ) . "', " .
                                          "'" . mysql_escape_string( $text         ) . "', " .
                                          "'" . mysql_escape_string( $authorName   ) . "', " .
                                          "'" . mysql_escape_string( $authorEmail  ) . "', " .
                                          "'" . date( "n/j/Y g:i:s A" )              . "', " .
                                          "'" . mysql_escape_string( $editLogEntry ) . "' "  .
                                      ")" );

        if ( ! $result )
        {
            throw new HardStoryException(
                    "Unable to insert the episode edit log into the database." );
        }

        // get the new EpisodeEditLogID from the database
        $result = mysql_query( "SELECT LAST_INSERT_ID()" );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to query the new EpisodeEditLogID." );
        }

        $row = mysql_fetch_row( $result );

        if ( ! $row )
        {
            throw new HardStoryException( "Unable to fetch the new EpisodeEditLogID row." );
        }

        $nextEpisodeEditLogID = $row[ 0 ];

        // read the options to log from the database
        $result = mysql_query( "SELECT TargetEpisodeID, " .
                                      "IsBackLink, " .
                                      "Description " .
                                 "FROM Link " .
                                "WHERE SourceEpisodeID = " . $episode . " " .
                                "ORDER BY LinkID" );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to query episode links from the database." );
        }

        for ( $i = 0; $i < mysql_num_rows( $result ); $i++ )
        {
            $row = mysql_fetch_row( $result );
            Util::createLinkEditLog( $nextEpisodeEditLogID, $row[ 0 ], $row[ 1 ], $row[ 2 ] );
        }

        return $nextEpisodeEditLogID;
    }

    public static function createLinkEditLog( $episodeEditLogID, $targetEpisodeID, $isBackLink,
                                              $description )
    {
        // insert the link edit log into the database
        $result = mysql_query( "INSERT " .
                                 "INTO LinkEditLog " .
                                      "( " .
                                          "EpisodeEditLogID, " .
                                          "TargetEpisodeID, " .
                                          "IsBackLink, " .
                                          "Description " .
                                      ") " .
                               "VALUES " .
                                      "( " .
                                                $episodeEditLogID                   .  ", " .
                                                $targetEpisodeID                    .  ", " .
                                          "'" . $isBackLink                         . "', " .
                                          "'" . mysql_escape_string( $description ) . "' " .
                                      ")" );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to insert the link edit log into the database." );
        }

        // get the new LinkEditLogID from the database
        $result = mysql_query( "SELECT LAST_INSERT_ID()" );

        if ( ! $result )
        {
            throw new HardStoryException( "Unable to query the new LinkEditLogID." );
        }

        $row = mysql_fetch_row( $result );

        if ( ! $row )
        {
            throw new HardStoryException( "Unable to fetch the new LinkEditLogID row." );
        }

        return $row[ 0 ];
    }

    public static function extensionNotification( $email, $parent, $episode, $authorName )
    {
        $storyName      = Util::getStringValue( "StoryName"      );
        $storyHome      = Util::getStringValue( "StoryHome"      );
        $readEpisodeURL = Util::getStringValue( "ReadEpisodeURL" );
        $adminEmail     = Util::getStringValue( "AdminEmail"     );

        $message = "This is an automated message.\n" .
                   "\n" .
                   "Episode " . $episode . ", a child of episode " . $parent .
                   ", has been created.\n" .
                   $readEpisodeURL . "?episode=" . $episode . "\n" .
                   "\n" .
                   "Author of the new episode: " . $authorName . "\n" .
                   "\n" .
                   "This email was automatically generated and sent because at some\n" .
                   "point you created one or more episodes in the expandable story\n" .
                   "          " . $storyName . "\n" .
                   "     " . $storyHome . "\n" .
                   "and asked to be notified when someone expanded your story line.";

        mail( $email, $storyName . " - Extension", $message,
              "From: " . $adminEmail, "-f" . $adminEmail );
    }
}

?>
