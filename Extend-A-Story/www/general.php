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

  function getSessionID( &$error, &$fatal )
  {
    $sessionID  = $_COOKIE[ "sessionID"  ];
    $sessionKey = $_COOKIE[ "sessionKey" ];

    $sessionID  = ( int ) $sessionID;
    $sessionKey = ( int ) $sessionKey;

    $actualSessionID = 0;

    $result = mysql_query( "select SessionKey from Session where SessionID = " . $sessionID );
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
        if ( $row[ 0 ] == $sessionKey )
        {
          $actualSessionID = $sessionID;

          $result = mysql_query( "update Session set AccessDate = now( ) " .
                                 "where SessionID = " . $sessionID );
          if ( ! $result )
          {
            $error .= "Problem updating your session in the database.<BR>";
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

      $sessionKey = mt_rand( );

      // Insert the session into the database.
      $result = mysql_query( "insert into Session values ( " .
                             $nextSessionID . ", " . $sessionKey . ", now( ) )" );
      if ( ! $result )
      {
        $error .= "Unable to insert the session into the database.<BR>";
        $fatal = true;
        return;
      }

      $actualSessionID = $nextSessionID;
    }

    setcookie( "sessionID",  $actualSessionID, time( ) + ( 60 * 60 * 24 * 370 ) );
    setcookie( "sessionKey", $sessionKey,      time( ) + ( 60 * 60 * 24 * 370 ) );

    // Delete all sessions over 370 days old.
    $result = mysql_query( "delete from Session where AccessDate < subdate( now( ), interval 370 day )" );
    if ( ! $result )
    {
      $error .= "Unable to delete old sessions from the database.<BR>";
      $fatal = true;
      return;
    }

    return $actualSessionID;
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

  function extensionNotification( &$error, &$fatal, $email, $parent, $episode )
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

?>
