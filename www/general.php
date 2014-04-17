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

function extensionNotification( &$error, &$fatal, $email, $parent, $episode, $authorName )
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
    if ( $userID != 0 )
    {
        return true;
    }

    $result = mysql_query( "SELECT AuthorSessionID, CreationDate " .
                             "FROM Episode " .
                            "WHERE EpisodeID = " . $episodeID );

    if ( ! $result )
    {
        return false;
    }

    $row = mysql_fetch_row( $result );

    if ( ! $row )
    {
        return false;
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

?>
