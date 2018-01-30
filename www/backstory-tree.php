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

require(  __DIR__ . "/include/Extend-A-Story.php" );

use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

Util::getSessionAndUserIDs( $sessionID, $userID );

$storyName = Util::getStringValue( "StoryName" );
$siteName  = Util::getStringValue( "SiteName"  );
$storyHome = Util::getStringValue( "StoryHome" );
$siteHome  = Util::getStringValue( "SiteHome"  );

$episode = Util::getIntParam( $_GET, "episode" );

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Back Story Tree</TITLE>
</HEAD><BODY>

<CENTER>
<H1><?php echo( $storyName ); ?>: Back Story Tree</H1>
<H2>
    Color Key for Parent Episodes:
    <FONT COLOR="#008000">First Time Encountered</FONT>,
    <FONT COLOR="#FF0000">Already Encountered</FONT>
</H2>
<H2>Bold episodes indicate the primary parent, all others are backlinks.</H2>

<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>
</CENTER>

<?php

$curLevel        = 0;
$curEpisodes     = array( $episode );
$visitedEpisodes = array();

while ( count( $curEpisodes ) > 0 )
{

?>

<H2>Level: <?php echo( $curLevel ); ?>, Episodes: <?php echo( count( $curEpisodes )); ?></H2>
<TABLE BORDER="1" CELLSPACING="0" WIDTH="100%">
    <TR>
        <TH width="50%">Episode Number and Title</TH>
        <TH width="50%">Parents of this Episode</TH>
    <TR>

<?php

    sort( $curEpisodes, SORT_NUMERIC );
    $nextEpisodes = array();

    for ( $i = 0; $i < count( $curEpisodes ); $i++ )
    {
        array_push( $visitedEpisodes, $curEpisodes[ $i ] );
    }

    for ( $i = 0; $i < count( $curEpisodes ); $i++ )
    {
        $episode = $curEpisodes[ $i ];

        $dbStatement = Util::getDbConnection()->prepare(
                "SELECT Title " .
                  "FROM Episode " .
                 "WHERE EpisodeID = :episode" );

        $dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( ! $row )
        {
            throw new StoryException( "Problem fetching episode row from database." );
        }

        $title  = $row[ 0 ];

        $dbStatement = Util::getDbConnection()->prepare(
                "SELECT SourceEpisodeID, " .
                       "IsBackLink " .
                  "FROM Link " .
                 "WHERE TargetEpisodeID = :episode " .
                 "ORDER BY SourceEpisodeID" );

        $dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
        $dbStatement->execute();
        $rows = $dbStatement->fetchAll( PDO::FETCH_NUM );

        $children = "";

        for ( $j = 0; $j < count( $rows ); $j++ )
        {
            $row = $rows[ $j ];
            $target = $row[ 0 ];
            $visited = in_array( $target, $visitedEpisodes );
            $isBackLink = $row[ 1 ];

            if ( $visited )
            {
                $color = "#FF0000";
            }
            else
            {
                $color = "#008000";

                if ( ! in_array( $target, $nextEpisodes ))
                {
                    array_push( $nextEpisodes, $target );
                }
            }

            $child = "<A HREF=\"read.php?episode=" . $target . "\"><FONT COLOR=\"" .
                     $color . "\">" . $target . "</FONT></A>";

            if ( $isBackLink == "N" )
            {
                $child = "<B>" . $child . "</B>";
            }

            if ( $j != 0 )
            {
                $children .= ", ";
            }

            $children .= $child;
        }

?>

    <TR>
        <TD>
<A HREF="backstory-tree.php?episode=<?php echo( $episode ); ?>">View Tree</A> -
<A HREF="read.php?episode=<?php echo( $episode ); ?>">
    <?php echo( $episode ); ?> : <?php echo( $title ); ?>
</A>
        </TD>
        <TD><?php echo( empty( $children ) ? "&nbsp;" : $children ); ?></TD>
    </TR>

<?php

    }

?>

</TABLE>

<?php

    $curEpisodes = $nextEpisodes;
    $curLevel++;
}

?>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>
