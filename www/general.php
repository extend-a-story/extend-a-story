<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002 - 2012  Jeffrey J. Weston, Matthew Duhan


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

function prepareParam( &$param )
{
    if ( get_magic_quotes_gpc() == 1 )
    {
        $param = stripslashes( $param );
    }

    $param = trim( $param );
}

function maximumWordLength( $input )
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

function displayError( $error, $fatal )
{
    echo( "<HTML><HEAD>" );
    echo( "<TITLE>Errors Detected</TITLE>" );
    echo( "</HEAD><BODY>" );

    echo( "<H1>Errors Detected</H1>" );

    if ( $fatal )
    {
        echo( "The following fatal errors have occurred. " );
        echo( "Please contact the site administrator." );
    }
    else
    {
        echo( "The following errors were detected with your submission. " );
        echo( "Please use your browser's back button, correct the errors, " );
        echo( "and try your submission again." );
    }

    echo( "<HR>" );
    echo( $error );

    exit;
}

function connectToDatabase( &$error, &$fatal )
{
    global $host;
    global $user;
    global $password;
    global $database;
    global $mysqli;

    $mysqli = mysqli_connect( $host, $user, $password, $database );
    if ( !$mysqli )
    {
        $error .= "Unable to connect to database.<BR>";
        $fatal = true;
        return;
    }
}

function getSessionAndUserIDs( &$error, &$fatal, &$sessionID, &$userID )
{
    global $mysqli;

    // log out all users after one hour of inactivity
    $result = mysqli_query( $mysqli,
                            "UPDATE Session " .
                               "SET UserID = 0 " .
                             "WHERE AccessDate < SUBDATE( NOW(), INTERVAL 1 HOUR )" );

    if ( ! $result )
    {
        $error .= "Unable to logout inactive users.<BR>";
        $fatal = true;
        return;
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

    $result = mysqli_query( $mysqli,
                            "SELECT UserID, SessionKey " .
                              "FROM Session " .
                             "WHERE SessionID = " . $originalSessionID );

    if ( ! $result )
    {
        $error .= "Problem retrieving your session from the database.<BR>";
        $fatal = true;
        return;
    }
    else
    {
        $row = mysqli_fetch_row( $result );

        if ( $row )
        {
            if ( $row[ 1 ] == $originalSessionKey )
            {
                $actualSessionID  = $originalSessionID;
                $actualUserID     = $row[ 0 ];
                $actualSessionKey = $originalSessionKey;

                $result = mysqli_query( $mysqli,
                                        "UPDATE Session " .
                                           "SET AccessDate = NOW() " .
                                         "WHERE SessionID = " . $originalSessionID );

                if ( ! $result )
                {
                    $error .= "Problem updating your session in the database.<BR>";
                    $fatal = true;
                }
            }
        }
    }

    if ( $actualSessionID == 0 )
    {
        // generate random session key
        $newSessionKey = mt_rand();

        // insert the session into the database
        $result = mysqli_query( $mysqli,
                                "INSERT " .
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
            $error .= "Unable to insert the session into the database.<BR>";
            $fatal = true;
            return;
        }

        // get the new SessionID from the database
        $result = mysqli_query( $mysqli, "SELECT LAST_INSERT_ID()" );

        if ( ! $result )
        {
            $error .= "Unable to query the new SessionID.<BR>";
            $fatal = true;
            return;
        }

        $row = mysqli_fetch_row( $result );

        if ( ! $row )
        {
            $error .= "Unable to fetch the new SessionID row.<BR>";
            $fatal = true;
            return;
        }

        $actualSessionID  = $row[ 0 ];
        $actualSessionKey = $newSessionKey;
    }

    setcookie( "sessionID",  $actualSessionID,  time() + ( 60 * 60 * 24 * 370 ));
    setcookie( "sessionKey", $actualSessionKey, time() + ( 60 * 60 * 24 * 370 ));

    // delete all sessions over 370 days old
    $result = mysqli_query( $mysqli,
                            "DELETE " .
                              "FROM Session " .
                             "WHERE AccessDate < SUBDATE( NOW(), INTERVAL 370 DAY )" );

    if ( ! $result )
    {
        $error .= "Unable to delete old sessions from the database.<BR>";
        $fatal = true;
        return;
    }

    $sessionID = $actualSessionID;
    $userID    = $actualUserID;
}

function setStringValue( &$error, &$fatal, $variableName, $variableValue )
{
    global $mysqli;

    $result = mysqli_query(
            $mysqli,
            "UPDATE ExtendAStoryVariable " .
               "SET StringValue = '" . mysqli_real_escape_string( $mysqli, $variableValue ) . "' " .
             "WHERE VariableName = '" . mysqli_real_escape_string( $mysqli, $variableName ) . "'" );

    if ( ! $result )
    {
        $error .= "Problem setting the " . $variableName . " value in the database.<BR>";
        $fatal = true;
    }
}

function setIntValue( &$error, &$fatal, $variableName, $variableValue )
{
    global $mysqli;

    $result = mysqli_query(
            $mysqli,
            "UPDATE ExtendAStoryVariable " .
               "SET IntValue = " . $variableValue . " " .
             "WHERE VariableName = '" . mysqli_real_escape_string( $mysqli, $variableName ) . "'" );

    if ( ! $result )
    {
        $error .= "Problem setting the " . $variableName . " value in the database.<BR>";
        $fatal = true;
    }
}

function getStringValue( &$error, &$fatal, $variableName )
{
    global $mysqli;

    $returnValue = "";

    $result = mysqli_query(
            $mysqli,
            "SELECT StringValue " .
              "FROM ExtendAStoryVariable " .
             "WHERE VariableName = '" . mysqli_real_escape_string( $mysqli, $variableName ) . "'" );

    if ( ! $result )
    {
        $error .= "Problem retrieving the " . $variableName . " value from the database.<BR>";
        $fatal = true;
    }
    else
    {
        $row = mysqli_fetch_row( $result );

        if ( ! $row )
        {
            $error .= "Problem fetching " . $variableName . " row from the database.<BR>";
            $fatal = true;
        }
        else
        {
            $returnValue = $row[ 0 ];
        }
    }

    return $returnValue;
}

function getIntValue( &$error, &$fatal, $variableName )
{
    return getIntValueInternal( $error, $fatal, $variableName, false );
}

function getAndIncrementIntValue( &$error, &$fatal, $variableName )
{
    return getIntValueInternal( $error, $fatal, $variableName, true );
}

function getIntValueInternal( &$error, &$fatal, $variableName, $increment )
{
    global $mysqli;

    if ( $increment )
    {
        // increment the value
        $result = mysqli_query(
                $mysqli,
                "UPDATE ExtendAStoryVariable " .
                   "SET IntValue = IntValue + 1 " .
                 "WHERE VariableName = '" . mysqli_real_escape_string( $mysqli, $variableName ) . "'" );

        if ( ! $result )
        {
            $error .= "Unable to increment the " . $variableName .
                      " value in the database.<BR>";
            $fatal = true;
            return 0;
        }
    }

    $returnValue = 0;

    $result = mysqli_query(
            $mysqli,
            "SELECT IntValue " .
              "FROM ExtendAStoryVariable " .
             "WHERE VariableName = '" . mysqli_real_escape_string( $mysqli, $variableName ) . "'" );

    if ( ! $result )
    {
        $error .= "Problem retrieving the " . $variableName . " value from the database.<BR>";
        $fatal = true;
    }
    else
    {
        $row = mysqli_fetch_row( $result );

        if ( ! $row )
        {
            $error .= "Problem fetching " . $variableName . " row from the database.<BR>";
            $fatal = true;
        }
        else
        {
            $returnValue = $row[ 0 ];
        }
    }

    return $returnValue;
}

function createEpisode( &$error, &$fatal, $parent, $scheme )
{
    global $mysqli;

    // insert the episode into the database
    $result = mysqli_query( $mysqli,
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
        $error .= "Unable to insert the episode into the database.<BR>";
        $fatal = true;
        return;
    }

    // get the new EpisodeID from the database
    $result = mysqli_query( $mysqli, "SELECT LAST_INSERT_ID()" );

    if ( ! $result )
    {
        $error .= "Unable to query the new EpisodeID.<BR>";
        $fatal = true;
        return;
    }

    $row = mysqli_fetch_row( $result );

    if ( ! $row )
    {
        $error .= "Unable to fetch the new EpisodeID row.<BR>";
        $fatal = true;
        return;
    }

    return $row[ 0 ];
}

function createLink( &$error, &$fatal, $sourceEpisode, $targetEpisode, $description,
                     $isBackLink )
{
    global $mysqli;

    $description = mysqli_real_escape_string( $mysqli, $description );

    // insert the link into the database
    $result = mysqli_query( $mysqli,
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
                                               $sourceEpisode            .  ", " .
                                               $targetEpisode            .  ", " .
                                       "'" . ( $isBackLink ? "Y" : "N" ) . "', " .
                                       "'" . ( $isBackLink ? "Y" : "N" ) . "', " .
                                       "'" .   $description              . "' "  .
                                   ")" );

    if ( ! $result )
    {
        $error .= "Unable to insert the link into the database.<BR>";
        $fatal = true;
        return;
    }
}

function createEpisodeEditLog( &$error, &$fatal, $episode, $editLogEntry )
{
    global $mysqli;

    // read the episode to log from the database
    $result = mysqli_query( $mysqli,
                            "SELECT SchemeID, ImageID, IsLinkable, IsExtendable, " .
                                   "AuthorMailto, AuthorNotify, Title, Text, AuthorName, " .
                                   "AuthorEmail " .
                              "FROM Episode WHERE EpisodeID = " . $episode );

    if ( ! $result )
    {
        $error .= "Unable to query original episode from database.<BR>";
        $fatal = true;
        return;
    }

    $row = mysqli_fetch_row( $result );

    if ( ! $row )
    {
        $error .= "Unable to fetch original episode row from database.<BR>";
        $fatal = true;
        return;
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
    $result = mysqli_query( $mysqli,
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
                            "VALUES " .
                                   "( " .
                                             $episode                                            .  ", " .
                                             $schemeID                                           .  ", " .
                                             $imageID                                            .  ", " .
                                       "'" . $isLinkable                                         . "', " .
                                       "'" . $isExtendable                                       . "', " .
                                       "'" . $authorMailto                                       . "', " .
                                       "'" . $authorNotify                                       . "', " .
                                       "'" . mysqli_real_escape_string( $mysqli, $title        ) . "', " .
                                       "'" . mysqli_real_escape_string( $mysqli, $text         ) . "', " .
                                       "'" . mysqli_real_escape_string( $mysqli, $authorName   ) . "', " .
                                       "'" . mysqli_real_escape_string( $mysqli, $authorEmail  ) . "', " .
                                       "'" . date( "n/j/Y g:i:s A" )                             . "', " .
                                       "'" . mysqli_real_escape_string( $mysqli, $editLogEntry ) . "' "  .
                                   ")" );

    if ( ! $result )
    {
        $error .= "Unable to insert the episode edit log into the database.<BR>";
        $fatal = true;
        return;
    }

    // get the new EpisodeEditLogID from the database
    $result = mysqli_query( $mysqli, "SELECT LAST_INSERT_ID()" );

    if ( ! $result )
    {
        $error .= "Unable to query the new EpisodeEditLogID.<BR>";
        $fatal = true;
        return;
    }

    $row = mysqli_fetch_row( $result );

    if ( ! $row )
    {
        $error .= "Unable to fetch the new EpisodeEditLogID row.<BR>";
        $fatal = true;
        return;
    }

    $nextEpisodeEditLogID = $row[ 0 ];

    // read the options to log from the database
    $result = mysqli_query( $mysqli,
                            "SELECT TargetEpisodeID, " .
                                   "IsBackLink, " .
                                   "Description " .
                              "FROM Link " .
                             "WHERE SourceEpisodeID = " . $episode . " " .
                             "ORDER BY LinkID" );

    if ( ! $result )
    {
        $error .= "Unable to query episode links from the database.<BR>";
        $fatal = true;
        return;
    }

    for ( $i = 0; $i < mysqli_num_rows( $result ); $i++ )
    {
        $row = mysqli_fetch_row( $result );
        createLinkEditLog( $error, $fatal, $nextEpisodeEditLogID,
                           $row[ 0 ], $row[ 1 ], $row[ 2 ] );
    }

    return $nextEpisodeEditLogID;
}

function createLinkEditLog( &$error, &$fatal, $episodeEditLogID, $targetEpisodeID, $isBackLink,
                            $description )
{
    global $mysqli;

    // insert the link edit log into the database
    $result = mysqli_query( $mysqli,
                            "INSERT " .
                              "INTO LinkEditLog " .
                                   "( " .
                                       "EpisodeEditLogID, " .
                                       "TargetEpisodeID, " .
                                       "IsBackLink, " .
                                       "Description " .
                                   ") " .
                            "VALUES " .
                                   "( " .
                                             $episodeEditLogID                                  .  ", " .
                                             $targetEpisodeID                                   .  ", " .
                                       "'" . $isBackLink                                        . "', " .
                                       "'" . mysqli_real_escape_string( $mysqli, $description ) . "' " .
                                   ")" );

    if ( ! $result )
    {
        $error .= "Unable to insert the link edit log into the database.<BR>";
        $fatal = true;
        return;
    }

    // get the new LinkEditLogID from the database
    $result = mysqli_query( $mysqli, "SELECT LAST_INSERT_ID()" );

    if ( ! $result )
    {
        $error .= "Unable to query the new LinkEditLogID.<BR>";
        $fatal = true;
        return;
    }

    $row = mysqli_fetch_row( $result );

    if ( ! $row )
    {
        $error .= "Unable to fetch the new LinkEditLogID row.<BR>";
        $fatal = true;
        return;
    }

    return $row[ 0 ];
}

function createUser( &$error, &$fatal, $permissionLevel, $loginName, $password, $userName )
{
    global $mysqli;

    // insert the user into the database
    $result = mysqli_query( $mysqli,
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
                                                       $permissionLevel                                 .    ", " .
                                                 "'" . mysqli_real_escape_string( $mysqli, $loginName ) .   "', " .
                                       "PASSWORD( '" . mysqli_real_escape_string( $mysqli, $password  ) . "' ), " .
                                                 "'" . mysqli_real_escape_string( $mysqli, $userName  ) .    "' " .
                                   ")" );

    if ( ! $result )
    {
        $error .= "Unable to insert the user into the database.<BR>";
        $fatal = true;
        return;
    }

    // get the new UserID from the database
    $result = mysqli_query( $mysqli, "SELECT LAST_INSERT_ID()" );

    if ( ! $result )
    {
        $error .= "Unable to query the new UserID.<BR>";
        $fatal = true;
        return;
    }

    $row = mysqli_fetch_row( $result );

    if ( ! $row )
    {
        $error .= "Unable to fetch the new UserID row.<BR>";
        $fatal = true;
        return;
    }

    return $row[ 0 ];
}

function extensionNotification( &$error, &$fatal, $email, $parent, $episode, $authorName )
{
    $storyName      = getStringValue( $error, $fatal, "StoryName"      );
    $storyHome      = getStringValue( $error, $fatal, "StoryHome"      );
    $readEpisodeURL = getStringValue( $error, $fatal, "ReadEpisodeURL" );
    $adminEmail     = getStringValue( $error, $fatal, "AdminEmail"     );

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

function getEpisodeBodyTranslationTable()
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

function getOptionTranslationTable()
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

function getEmailAddressTranslationTable()
{
    return array( "\"" => "'",
                  "@"  => " at ",
                  "."  => " dot " );
}

function canEditEpisode( $sessionID, $userID, $episodeID )
{
    global $mysqli;

    if ( $userID != 0 )
    {
        return true;
    }

    $result = mysqli_query( $mysqli,
                            "SELECT AuthorSessionID, CreationDate " .
                              "FROM Episode " .
                             "WHERE EpisodeID = " . $episodeID );

    if ( ! $result )
    {
        return false;
    }

    $row = mysqli_fetch_row( $result );

    if ( ! $row )
    {
        return false;
    }

    $authorSessionID = $row[ 0 ];
    $creationDate    = $row[ 1 ];

    if ( $sessionID == $authorSessionID )
    {
        $error = "";
        $fatal = false;

        $maxEditDays = getIntValue( $error, $fatal, "MaxEditDays" );

        if ( ! empty( $error ))
        {
            return false;
        }

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

?>
