<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002  Jeff Weston


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
    if ( get_magic_quotes_gpc( ) == 1 )
    {
      $param = stripslashes( $param );
    }
    $param = trim( $param );
  }

  function maximumWordLength( $input )
  {
    $result = 0;

    $word = strtok( $input, " \t\n\r\0\x0B" );

    while( ! ( $word === false ) )
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
      echo( "The following fatal errors have occurred. Please contact the site administrator." );
    }
    else
    {
      echo( "The following errors were detected with your submission." );
      echo( "Please use your browser's back button, correct the errors, and try your submission again." );
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

    if ( ! mysql_connect( $host, $user, $password ) )
    {
      $error .= "Unable to connect to database.<BR>";
      $fatal = true;
      return;
    }
    if ( ! mysql_select_db( $database ) )
    {
      $error .= "Unable to select ExtendAStory database.<BR>";
      $fatal = true;
    }
  }

  function getSessionAndUserIDs( &$error, &$fatal, &$sessionID, &$userID )
  {
    // Logout all users after one hour of inactivity.
    $result = mysql_query( "update Session set UserID = 0 where AccessDate < subdate( now( ), interval 1 hour )" );
    if ( ! $result )
    {
      $error .= "Unable to logout inactive users.<BR>";
      $fatal = true;
      return;
    }

    $originalSessionID  = $_COOKIE[ "sessionID"  ];
    $originalSessionKey = $_COOKIE[ "sessionKey" ];

    $originalSessionID  = ( int ) $originalSessionID;
    $originalSessionKey = ( int ) $originalSessionKey;

    $actualSessionID  = 0;
    $actualUserID     = 0;
    $actualSessionKey = 0;

    $result = mysql_query( "select UserID, SessionKey from Session where SessionID = " . $originalSessionID );
    if ( ! $result )
    {
      $error .= "Problem retrieving your session from the database.<BR>";
      $fatal = true;
      return;
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

          $result = mysql_query( "update Session set AccessDate = now( ) " .
                                 "where SessionID = " . $originalSessionID );
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
      // Get the NextSessionID from the database.
      $result = mysql_query( "select IntValue from ExtendAStoryVariable " .
                             "where VariableName = 'NextSessionID'" );
      if ( ! $result )
      {
        $error .= "Unable to query the NextSessionID.<BR>";
        $fatal = true;
        return;
      }

      $row = mysql_fetch_row( $result );
      if ( ! $row )
      {
        $error .= "Unable to fetch the NextSessionID row.<BR>";
        $fatal = true;
        return;
      }

      $nextSessionID = $row[ 0 ];

      // Update the NextSessionID in the database.
      $result = mysql_query( "update ExtendAStoryVariable set " .
                             "IntValue = IntValue + 1 " .
                             "where VariableName = 'NextSessionID'" );
      if ( ! $result )
      {
        $error .= "Unable to update the NextSessionID.<BR>";
        $fatal = true;
        return;
      }

      $newSessionKey = mt_rand( );

      // Insert the session into the database.
      $result = mysql_query( "insert into Session values( " .
                             $nextSessionID . ", 0, " . $newSessionKey . ", now( ) )" );
      if ( ! $result )
      {
        $error .= "Unable to insert the session into the database.<BR>";
        $fatal = true;
        return;
      }

      $actualSessionID  = $nextSessionID;
      $actualSessionKey = $newSessionKey;
    }

    setcookie( "sessionID",  $actualSessionID,  time( ) + ( 60 * 60 * 24 * 370 ) );
    setcookie( "sessionKey", $actualSessionKey, time( ) + ( 60 * 60 * 24 * 370 ) );

    // Delete all sessions over 370 days old.
    $result = mysql_query( "delete from Session where AccessDate < subdate( now( ), interval 370 day )" );
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
    $result = mysql_query( "update ExtendAStoryVariable set StringValue = " .
                           "'" . mysql_escape_string( $variableValue ) . "' " .
                           "where VariableName = '" . $variableName . "'" );
    if ( ! $result )
    {
      $error .= "Problem setting the " . $variableName . " value in the database.<BR>";
      $fatal = true;
    }
  }

  function setIntValue( &$error, &$fatal, $variableName, $variableValue )
  {
    $result = mysql_query( "update ExtendAStoryVariable set IntValue = " .
                           $variableValue . " " .
                           "where VariableName = '" . $variableName . "'" );
    if ( ! $result )
    {
      $error .= "Problem setting the " . $variableName . " value in the database.<BR>";
      $fatal = true;
    }
  }

  function getStringValue( &$error, &$fatal, $variableName )
  {
    $returnValue = "";

    $result = mysql_query( "select StringValue from ExtendAStoryVariable where VariableName = '" . $variableName . "'" );
    if ( ! $result )
    {
      $error .= "Problem retrieving the " . $variableName . " value from the database.<BR>";
      $fatal = true;
    }
    else
    {
      $row = mysql_fetch_row( $result );
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
    if ( $increment )
    {
      // Increment the value.
      $result = mysql_query( "update ExtendAStoryVariable set " .
                             "IntValue = IntValue + 1 " .
                             "where VariableName = '" . $variableName . "'" );

      if ( ! $result )
      {
        $error .= "Unable to increment the " . $variableName . " value in the database.<BR>";
        $fatal = true;
      }
    }

    $returnValue = 0;

    $result = mysql_query( "select IntValue from ExtendAStoryVariable where VariableName = '" . $variableName . "'" );
    if ( ! $result )
    {
      $error .= "Problem retrieving the " . $variableName . " value from the database.<BR>";
      $fatal = true;
    }
    else
    {
      $row = mysql_fetch_row( $result );
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
    // Get the NextEpisodeID from the database.
    $result = mysql_query( "select IntValue from ExtendAStoryVariable " .
                           "where VariableName = 'NextEpisodeID'" );
    if ( ! $result )
    {
      $error .= "Unable to query the NextEpisodeID.<BR>";
      $fatal = true;
      return;
    }

    $row = mysql_fetch_row( $result );
    if ( ! $row )
    {
      $error .= "Unable to fetch the NextEpisodeID row.<BR>";
      $fatal = true;
      return;
    }

    $nextEpisodeID = $row[ 0 ];

    // Update the NextEpisodeID in the database.
    $result = mysql_query( "update ExtendAStoryVariable set " .
                           "IntValue = IntValue + 1 " .
                           "where VariableName = 'NextEpisodeID'" );
    if ( ! $result )
    {
      $error .= "Unable to update the NextEpisodeID.<BR>";
      $fatal = true;
      return;
    }

    // Insert the episode into the database.
    $result = mysql_query( "insert into Episode values ( " . $nextEpisodeID . ", " . $parent . ", 0, 0, " . $scheme .
                           ", 0, 0, 'N', 'N', 'N', 'N', '-', '-', '-', '-', '-', '-', 0, null )" );
    if ( ! $result )
    {
      $error .= "Unable to insert the episode into the database.<BR>";
      $fatal = true;
      return;
    }
    return $nextEpisodeID;
  }

  function createLink( &$error, &$fatal, $sourceEpisode, $targetEpisode, $description, $isBackLink )
  {
    $description = mysql_escape_string( $description );

    // Get the NextLinkID from the database.
    $result = mysql_query( "select IntValue from ExtendAStoryVariable " .
                           "where VariableName = 'NextLinkID'" );
    if ( ! $result )
    {
      $error .= "Unable to query the NextLinkID.<BR>";
      $fatal = true;
      return;
    }

    $row = mysql_fetch_row( $result );
    if ( ! $row )
    {
      $error .= "Unable to fetch the NextLinkID row.<BR>";
      $fatal = true;
      return;
    }

    $nextLinkID = $row[ 0 ];

    // Update the NextLinkID in the database.
    $result = mysql_query( "update ExtendAStoryVariable set " .
                           "IntValue = IntValue + 1 " .
                           "where VariableName = 'NextLinkID'" );
    if ( ! $result )
    {
      $error .= "Unable to update the NextLinkID.<BR>";
      $fatal = true;
      return;
    }

    // Insert the link into the database.
    $result = mysql_query( "insert into Link values ( " . $nextLinkID                 .  ", " .
                                                          $sourceEpisode              .  ", " .
                                                          $targetEpisode              .  ", " .
                                                    "'" . ( $isBackLink ? "Y" : "N" ) . "', " .
                                                    "'" . ( $isBackLink ? "Y" : "N" ) . "', " .
                                                    "'" . $description                . "' )" );
    if ( ! $result )
    {
      $error .= "Unable to insert the link into the database.<BR>";
      $fatal = true;
      return;
    }
  }

  function createEpisodeEditLog( &$error, &$fatal, $episode, $editLogEntry )
  {
    // Read the episode to log from the database.
    $result = mysql_query( "select SchemeID, ImageID, IsLinkable, IsExtendable, AuthorMailto, AuthorNotify, " .
                                  "Title, Text, AuthorName, AuthorEmail from Episode where EpisodeID = " . $episode );
    if ( ! $result )
    {
      $error .= "Unable to query original episode from database.<BR>";
      $fatal = true;
      return;
    }

    $row = mysql_fetch_row( $result );
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

    // Get the NextEpisodeEditLogID from the database.
    $result = mysql_query( "select IntValue from ExtendAStoryVariable " .
                           "where VariableName = 'NextEpisodeEditLogID'" );
    if ( ! $result )
    {
      $error .= "Unable to query the NextEpisodeEditLogID.<BR>";
      $fatal = true;
      return;
    }

    $row = mysql_fetch_row( $result );
    if ( ! $row )
    {
      $error .= "Unable to fetch the NextEpisodeEditLogID row.<BR>";
      $fatal = true;
      return;
    }

    $nextEpisodeEditLogID = $row[ 0 ];

    // Update the NextEpisodeEditLogID in the database.
    $result = mysql_query( "update ExtendAStoryVariable set " .
                           "IntValue = IntValue + 1 " .
                           "where VariableName = 'NextEpisodeEditLogID'" );
    if ( ! $result )
    {
      $error .= "Unable to update the NextEpisodeEditLogID.<BR>";
      $fatal = true;
      return;
    }

    // Insert the episode edit log into the database.
    $result = mysql_query( "insert into EpisodeEditLog values( " .
                                 $nextEpisodeEditLogID                .  ", " .
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
                           "'" . mysql_escape_string( $editLogEntry ) . "' )" );
    if ( ! $result )
    {
      $error .= "Unable to insert the episode edit log into the database.<BR>";
      $fatal = true;
      return;
    }

    // Read the options to log from the database.
    $result = mysql_query( "select TargetEpisodeID, IsBackLink, Description from Link where SourceEpisodeID = " .
                           $episode . " order by LinkID" );
    if ( ! $result )
    {
      $error .= "Unable to query episode links from the database.<BR>";
      $fatal = true;
      return;
    }

    for ( $i = 0; $i < mysql_num_rows( $result ); $i++ )
    {
      $row = mysql_fetch_row( $result );
      createLinkEditLog( $error, $fatal, $nextEpisodeEditLogID, $row[ 0 ], $row[ 1 ], $row[ 2 ] );
    }

    return $nextEpisodeEditLogID;
  }

  function createLinkEditLog( &$error, &$fatal, $episodeEditLogID, $targetEpisodeID, $isBackLink, $description )
  {
    // Get the NextLinkEditLogID from the database.
    $result = mysql_query( "select IntValue from ExtendAStoryVariable " .
                           "where VariableName = 'NextLinkEditLogID'" );
    if ( ! $result )
    {
      $error .= "Unable to query the NextLinkEditLogID.<BR>";
      $fatal = true;
      return;
    }

    $row = mysql_fetch_row( $result );
    if ( ! $row )
    {
      $error .= "Unable to fetch the NextLinkEditLogID row.<BR>";
      $fatal = true;
      return;
    }

    $nextLinkEditLogID = $row[ 0 ];

    // Update the NextLinkEditLogID in the database.
    $result = mysql_query( "update ExtendAStoryVariable set " .
                           "IntValue = IntValue + 1 " .
                           "where VariableName = 'NextLinkEditLogID'" );
    if ( ! $result )
    {
      $error .= "Unable to update the NextLinkEditLogID.<BR>";
      $fatal = true;
      return;
    }

    // Insert the link edit log into the database.
    $result = mysql_query( "insert into LinkEditLog values( " .
                                 $nextLinkEditLogID                  .  ", " .
                                 $episodeEditLogID                   .  ", " .
                                 $targetEpisodeID                    .  ", " .
                           "'" . $isBackLink                         . "', " .
                           "'" . mysql_escape_string( $description ) . "' )" );
    if ( ! $result )
    {
      $error .= "Unable to insert the link edit log into the database.<BR>";
      $fatal = true;
      return;
    }
    return $nextLinkEditLogID;
  }

  function createUser( &$error, &$fatal, $permissionLevel, $loginName, $password, $userName )
  {
    // Get the NextUserID from the database.
    $result = mysql_query( "select IntValue from ExtendAStoryVariable " .
                           "where VariableName = 'NextUserID'" );
    if ( ! $result )
    {
      $error .= "Unable to query the NextUserID.<BR>";
      $fatal = true;
      return;
    }

    $row = mysql_fetch_row( $result );
    if ( ! $row )
    {
      $error .= "Unable to fetch the NextUserID row.<BR>";
      $fatal = true;
      return;
    }

    $nextUserID = $row[ 0 ];

    // Update the NextUserID in the database.
    $result = mysql_query( "update ExtendAStoryVariable set " .
                           "IntValue = IntValue + 1 " .
                           "where VariableName = 'NextUserID'" );
    if ( ! $result )
    {
      $error .= "Unable to update the NextUserID.<BR>";
      $fatal = true;
      return;
    }

    // Insert the user into the database.
    $result = mysql_query( "insert into User values( " .
                                 $nextUserID                      .  ", " .
                                 $permissionLevel                 .  ", " .
                           "'" . $loginName                       . "', " .
                 "password( '" . mysql_escape_string( $password ) . "' ), " .
                           "'" . mysql_escape_string( $userName ) . "' )" );
    if ( ! $result )
    {
      $error .= "Unable to insert the user into the database.<BR>";
      $fatal = true;
      return;
    }
    return $nextUserID;
  }

  function extensionNotification( &$error, &$fatal, $email, $parent, $episode, $authorName )
  {
    $storyName      = getStringValue( $error, $fatal, "StoryName"      );
    $storyHome      = getStringValue( $error, $fatal, "StoryHome"      );
    $readEpisodeURL = getStringValue( $error, $fatal, "ReadEpisodeURL" );
    $adminEmail     = getStringValue( $error, $fatal, "AdminEmail"     );

    $message =
      "This is an automated message.\n" .
      "\n" .
      "Episode " . $episode . ", a child of episode " . $parent . ", has been created.\n" .
      $readEpisodeURL . "?episode=" . $episode . "\n" .
      "\n" .
      "Author of the new episode: " . $authorName . "\n" .
      "\n" .
      "This email was automatically generated and sent because at some\n" .
      "point you created one or more episodes in the expandable story\n" .
      "          " . $storyName . "\n" .
      "     " . $storyHome . "\n" .
      "and asked to be notified when someone expanded your storyline.";

      mail( $email, $storyName . " - Extension", $message, "From: " . $adminEmail, "-f" . $adminEmail );
  }

  function getEpisodeBodyTranslationTable( )
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

  function getOptionTranslationTable( )
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

  function getEmailAddressTranslationTable( )
  {
    return array( "\"" => "'" );
  }

  function canEditEpisode( $sessionID, $userID, $episodeID )
  {
    if ( $userID != 0 )
      return true;

    $result = mysql_query( "select AuthorSessionID, CreationDate from Episode where EpisodeID = " . $episodeID );
    if ( ! $result )
      return false;

    $row = mysql_fetch_row( $result );
    if ( ! $row )
      return false;

    $authorSessionID = $row[ 0 ];
    $creationDate    = $row[ 1 ];

    if ( $sessionID == $authorSessionID )
    {
      $error = "";
      $fatal = false;

      $maxEditDays = getIntValue( $error, $fatal, "MaxEditDays" );

      if ( ! empty( $error ) )
        return false;

      $creationTime = strtotime( $creationDate );
      $curTime      = time( );
      $seconds      = $curTime - $creationTime;
      $minutes      = $seconds / 60;
      $minutes      = ( int ) $minutes;
      $hours        = $minutes / 60;
      $hours        = ( int ) $hours;
      $days         = $hours / 24;
      $days         = ( int ) $days;

      if ( $days < $maxEditDays )
        return true;
    }

    return false;
  }

?>
