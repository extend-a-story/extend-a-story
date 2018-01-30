<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2018 Jeffrey J. Weston <jjweston@gmail.com>


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

namespace Extend_A_Story;

use \PDO;

class Util
{
    private static $dbConnection = null;

    public static function verifyDbConfiguration()
    {
        global $dbHost, $dbUser, $dbPassword, $dbDatabase;

        if (( ! isset( $dbHost     )) ||
            ( ! isset( $dbUser     )) ||
            ( ! isset( $dbPassword )) ||
            ( ! isset( $dbDatabase )))
        {
            throw new StoryException(
                    "The Extend-A-Story installation is not complete. If you are the " .
                    "administrator, please refer to the documentation for completing the " .
                    "installation." );
        }
    }

    public static function getDbConnection()
    {
        if ( ! isset( Util::$dbConnection ))
        {
            global $dbHost, $dbUser, $dbPassword, $dbDatabase;

            Util::verifyDbConfiguration();

            $dbOptions = array();
            $dbOptions[ PDO::MYSQL_ATTR_FOUND_ROWS ] = true;

            Util::$dbConnection = new PDO( "mysql:host=" . $dbHost . ";dbname=" . $dbDatabase,
                                           $dbUser, $dbPassword, $dbOptions );

            Util::$dbConnection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }

        return Util::$dbConnection;
    }

    public static function getLastInsertId()
    {
        $dbStatement = Util::getDbConnection()->prepare( "SELECT LAST_INSERT_ID()" );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( ! $row )
        {
            throw new StoryException( "Unable to fetch the last insert ID." );
        }

        return $row[ 0 ];
    }

    public static function getStringParam( $array, $name )
    {
        $value = Util::getStringParamDefault( $array, $name, null );

        if ( ! isset( $value ))
        {
            throw new StoryException( "Parameter \"" . $name . "\" is not set." );
        }

        return $value;
    }

    public static function getStringParamDefault( $array, $name, $defaultValue )
    {
        if ( ! isset( $array[ $name ] ))
        {
            return $defaultValue;
        }

        return trim( $array[ $name ] );
    }

    public static function getIntParam( $array, $name )
    {
        $value = Util::getIntParamDefault( $array, $name, null );

        if ( ! isset( $value ))
        {
            throw new StoryException( "Parameter \"" . $name . "\" is not set." );
        }

        return $value;
    }

    public static function getIntParamDefault( $array, $name, $defaultValue )
    {
        if ( ! isset( $array[ $name ] ))
        {
            return $defaultValue;
        }

        $value = trim( $array[ $name ] );

        if ( empty( $value ))
        {
            return $defaultValue;
        }

        if ( ! ctype_digit( $value ))
        {
            throw new StoryException( "Parameter \"" . $name . "\" is not an integer." );
        }

        return (int) $value;
    }

    public static function maximumWordLength( $input )
    {
        $result = 0;

        $word = strtok( $input, " \t\n\r\0\x0B" );

        while( ! ( $word === false ))
        {
            if ( strlen( $word ) > $result )
            {
                $result = strlen( $word );
            }

            $word = strtok( " \t\n\r\0\x0B" );
        }

        return $result;
    }

    public static function getEpisodeBodyTranslation()
    {
        return array( "&lt;P&gt;"  => "<P>",
                      "&lt;p&gt;"  => "<p>",
                      "&lt;/P&gt;" => "</P>",
                      "&lt;/p&gt;" => "</p>",
                      "&lt;BR&gt;" => "<BR>",
                      "&lt;bR&gt;" => "<bR>",
                      "&lt;Br&gt;" => "<Br>",
                      "&lt;br&gt;" => "<br>",
                      "&lt;HR&gt;" => "<HR>",
                      "&lt;hR&gt;" => "<hR>",
                      "&lt;Hr&gt;" => "<Hr>",
                      "&lt;hr&gt;" => "<hr>",
                      "&lt;B&gt;"  => "<B>",
                      "&lt;b&gt;"  => "<b>",
                      "&lt;/B&gt;" => "</B>",
                      "&lt;/b&gt;" => "</b>",
                      "&lt;I&gt;"  => "<I>",
                      "&lt;i&gt;"  => "<i>",
                      "&lt;/I&gt;" => "</I>",
                      "&lt;/i&gt;" => "</i>" );
    }

    public static function getOptionTranslation()
    {
        return array( "&lt;B&gt;"  => "<B>",
                      "&lt;b&gt;"  => "<b>",
                      "&lt;/B&gt;" => "</B>",
                      "&lt;/b&gt;" => "</b>",
                      "&lt;I&gt;"  => "<I>",
                      "&lt;i&gt;"  => "<i>",
                      "&lt;/I&gt;" => "</I>",
                      "&lt;/i&gt;" => "</i>" );
    }

    public static function getEmailAddressTranslation()
    {
        return array( "\"" => "'",
                      "@"  => " at ",
                      "."  => " dot " );
    }

    public static function getSessionAndUserIDs( &$sessionID, &$userID )
    {
        // log out all users after one hour of inactivity
        $dbStatement = Util::getDbConnection()->prepare(
                "UPDATE Session " .
                   "SET UserID = 0 " .
                 "WHERE AccessDate < SUBDATE( NOW(), INTERVAL 1 HOUR )" );

        $dbStatement->execute();

        $originalSessionID  = Util::getIntParamDefault( $_COOKIE, "sessionID",  0 );
        $originalSessionKey = Util::getIntParamDefault( $_COOKIE, "sessionKey", 0 );

        $actualSessionID  = 0;
        $actualUserID     = 0;
        $actualSessionKey = 0;

        $dbStatement = Util::getDbConnection()->prepare( "SELECT UserID, " .
                                                                "SessionKey " .
                                                           "FROM Session " .
                                                          "WHERE SessionID = :originalSessionID" );

        $dbStatement->bindParam( ":originalSessionID", $originalSessionID, PDO::PARAM_INT );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( $row )
        {
            if ( $row[ 1 ] == $originalSessionKey )
            {
                $actualSessionID  = $originalSessionID;
                $actualUserID     = $row[ 0 ];
                $actualSessionKey = $originalSessionKey;

                $dbStatement = Util::getDbConnection()->prepare(
                        "UPDATE Session " .
                           "SET AccessDate = NOW() " .
                         "WHERE SessionID = :originalSessionID" );

                $dbStatement->bindParam( ":originalSessionID", $originalSessionID, PDO::PARAM_INT );
                $dbStatement->execute();

                if ( $dbStatement->rowCount() != 1 )
                {
                    throw new StoryException( "Unable to update your session." );
                }
            }
        }

        if ( $actualSessionID == 0 )
        {
            // generate random session key
            $newSessionKey = mt_rand();

            // insert the session into the database
            $dbStatement = Util::getDbConnection()->prepare( "INSERT " .
                                                               "INTO Session " .
                                                                    "( " .
                                                                        "UserID, " .
                                                                        "SessionKey, " .
                                                                        "AccessDate " .
                                                                    ") " .
                                                             "VALUES ".
                                                                    "( " .
                                                                        "0, " .
                                                                        ":newSessionKey, " .
                                                                        "NOW() " .
                                                                    ")" );

            $dbStatement->bindParam( ":newSessionKey", $newSessionKey, PDO::PARAM_INT );

            $dbStatement->execute();

            $actualSessionID  = Util::getLastInsertId();
            $actualSessionKey = $newSessionKey;
        }

        setcookie( "sessionID",  $actualSessionID,  time() + ( 60 * 60 * 24 * 370 ));
        setcookie( "sessionKey", $actualSessionKey, time() + ( 60 * 60 * 24 * 370 ));

        // delete all sessions over 370 days old
        $dbStatement = Util::getDbConnection()->prepare(
                "DELETE " .
                  "FROM Session " .
                 "WHERE AccessDate < SUBDATE( NOW(), INTERVAL 370 DAY )" );

        $dbStatement->execute();

        $sessionID = $actualSessionID;
        $userID    = $actualUserID;
    }

    public static function getStringValue( $variableName )
    {
        $dbStatement = Util::getDbConnection()->prepare( "SELECT StringValue " .
                                                           "FROM ExtendAStoryVariable " .
                                                          "WHERE VariableName = :variableName" );

        $dbStatement->bindParam( ":variableName", $variableName, PDO::PARAM_STR );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( ! $row )
        {
            throw new StoryException(
                    "Unable to fetch \"" . $variableName . "\" string value." );
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
            $dbStatement = Util::getDbConnection()->prepare(
                    "UPDATE ExtendAStoryVariable " .
                       "SET IntValue = IntValue + 1 " .
                     "WHERE VariableName = :variableName" );

            $dbStatement->bindParam( ":variableName", $variableName, PDO::PARAM_STR );
            $dbStatement->execute();

            if ( $dbStatement->rowCount() != 1 )
            {
                throw new StoryException(
                        "Unable to increment \"" . $variableName . "\" int value." );
            }
        }

        $dbStatement = Util::getDbConnection()->prepare( "SELECT IntValue " .
                                                           "FROM ExtendAStoryVariable " .
                                                          "WHERE VariableName = :variableName" );

        $dbStatement->bindParam( ":variableName", $variableName, PDO::PARAM_STR );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( ! $row )
        {
            throw new StoryException(
                    "Unable to fetch \"" . $variableName . "\" int value." );
        }

        return $row[ 0 ];
    }

    public static function setStringValue( $variableName, $stringValue )
    {
        $dbStatement = Util::getDbConnection()->prepare(
                "UPDATE ExtendAStoryVariable " .
                   "SET StringValue = :stringValue " .
                 "WHERE VariableName = :variableName" );

        $dbStatement->bindParam( ":stringValue",  $stringValue,  PDO::PARAM_STR );
        $dbStatement->bindParam( ":variableName", $variableName, PDO::PARAM_STR );
        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException(
                    "Unable to set \"" . $variableName . "\" string value." );
        }
    }

    public static function setIntValue( $variableName, $intValue )
    {
        $dbStatement = Util::getDbConnection()->prepare(
                "UPDATE ExtendAStoryVariable " .
                   "SET IntValue = :intValue " .
                 "WHERE VariableName = :variableName" );

        $dbStatement->bindParam( ":intValue",     $intValue,     PDO::PARAM_INT );
        $dbStatement->bindParam( ":variableName", $variableName, PDO::PARAM_STR );
        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException(
                    "Unable to set \"" . $variableName . "\" int value." );
        }
    }

    public static function createUser( $permissionLevel, $loginName, $password, $userName )
    {
        // insert the user into the database
        $dbStatement = Util::getDbConnection()->prepare(
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
                           ":permissionLevel, " .
                           ":loginName, " .
                           "PASSWORD( :password ), " .
                           ":userName " .
                       ")" );

        $dbStatement->bindParam( ":permissionLevel", $permissionLevel, PDO::PARAM_INT );
        $dbStatement->bindParam( ":loginName",       $loginName,       PDO::PARAM_STR );
        $dbStatement->bindParam( ":password",        $password,        PDO::PARAM_STR );
        $dbStatement->bindParam( ":userName",        $userName,        PDO::PARAM_STR );

        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException( "Unable to insert the user into the database." );
        }

        return Util::getLastInsertId();
    }

    public static function createEpisode( $parent, $scheme )
    {
        // insert the episode into the database
        $dbStatement = Util::getDbConnection()->prepare(
                "INSERT " .
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
                           ":parent, " .
                           "0, " .
                           "0, " .
                           ":scheme, " .
                           "0, " .
                           "0, " .
                           "'N', " .
                           "'N', " .
                           "'N', " .
                           "'N', " .
                           "'-', " .
                           "'-', " .
                           "'-', " .
                           "'-', " .
                           "'-', " .
                           "'-', " .
                           "0, " .
                           "null " .
                       ")" );

        $dbStatement->bindParam( ":parent", $parent, PDO::PARAM_INT );
        $dbStatement->bindParam( ":scheme", $scheme, PDO::PARAM_INT );

        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException( "Unable to insert the episode into the database." );
        }

        return Util::getLastInsertId();
    }

    public static function createLink( $sourceEpisode, $targetEpisode, $description, $isBackLink )
    {
        $isBackLink = ( $isBackLink ? "Y" : "N" );

        // insert the link into the database
        $dbStatement = Util::getDbConnection()->prepare(
                "INSERT " .
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
                           ":sourceEpisode, " .
                           ":targetEpisode, " .
                           ":isBackLink, " .
                           ":isBackLink, " .
                           ":description " .
                       ")" );

        $dbStatement->bindParam( ":sourceEpisode", $sourceEpisode, PDO::PARAM_INT );
        $dbStatement->bindParam( ":targetEpisode", $targetEpisode, PDO::PARAM_INT );
        $dbStatement->bindParam( ":isBackLink",    $isBackLink,    PDO::PARAM_STR );
        $dbStatement->bindParam( ":description",   $description,   PDO::PARAM_STR );

        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException( "Unable to insert the link into the database." );
        }
    }

    public static function createEpisodeEditLog( $episode, $editLogEntry )
    {
        $editDate = date( "n/j/Y g:i:s A" );

        // insert the episode edit log into the database
        $dbStatement = Util::getDbConnection()->prepare(
                "INSERT " .
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
                "SELECT EpisodeID, " .
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
                       ":editDate, " .
                       ":editLogEntry " .
                  "FROM Episode " .
                 "WHERE EpisodeID = :episode" );

        $dbStatement->bindParam( ":editDate",     $editDate,     PDO::PARAM_STR );
        $dbStatement->bindParam( ":editLogEntry", $editLogEntry, PDO::PARAM_STR );
        $dbStatement->bindParam( ":episode",      $episode,      PDO::PARAM_INT );

        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException(
                    "Unable to insert the episode edit log into the database." );
        }

        $episodeEditLogID = Util::getLastInsertId();

        // insert the link edit log into the database
        $dbStatement = Util::getDbConnection()->prepare(
                "INSERT " .
                  "INTO LinkEditLog " .
                       "( " .
                           "EpisodeEditLogID, " .
                           "TargetEpisodeID, " .
                           "IsBackLink, " .
                           "Description " .
                       ") " .
                "SELECT :episodeEditLogID, " .
                       "TargetEpisodeID, " .
                       "IsBackLink, " .
                       "Description " .
                  "FROM Link " .
                 "WHERE SourceEpisodeID = :episode " .
                 "ORDER BY LinkID" );

        $dbStatement->bindParam( ":episodeEditLogID", $episodeEditLogID, PDO::PARAM_INT );
        $dbStatement->bindParam( ":episode",          $episode,          PDO::PARAM_INT );

        $dbStatement->execute();

        return $episodeEditLogID;
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

    public static function canEditEpisode( $sessionID, $userID, $episodeID )
    {
        if ( $userID != 0 )
        {
            return true;
        }

        $dbStatement = Util::getDbConnection()->prepare( "SELECT AuthorSessionID, " .
                                                                "CreationDate " .
                                                           "FROM Episode " .
                                                          "WHERE EpisodeID = :episodeID" );

        $dbStatement->bindParam( ":episodeID", $episodeID, PDO::PARAM_INT );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( ! $row )
        {
            throw new StoryException( "Episode " . $episodeID . " not found." );
        }

        $authorSessionID = $row[ 0 ];
        $creationDate    = $row[ 1 ];

        if ( $sessionID == $authorSessionID )
        {
            $maxEditDays = Util::getIntValue( "MaxEditDays" );

            $creationTime = strtotime( $creationDate );
            $curTime      = time();
            $seconds      = $curTime - $creationTime;
            $minutes      = (int) ( $seconds / 60 );
            $hours        = (int) ( $minutes / 60 );
            $days         = (int) ( $hours   / 24 );

            if ( $days < $maxEditDays )
            {
                return true;
            }
        }

        return false;
    }
}

?>
