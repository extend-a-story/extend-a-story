<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2013 Jeffrey J. Weston and Matthew Duhan


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

require( "ExtendAStory.php" );

$error = "";
$fatal = false;

$episode = 1;

if ( isset( $_GET[ "episode" ] ))
{
    $episode = (int) $_GET[ "episode" ];
}

// connect to the database
if ( empty( $error ))
{
    connectToDatabase( $error, $fatal );
}

if ( empty( $error ))
{
    getSessionAndUserIDs( $error, $fatal, $sessionID, $userID );
}

if ( empty( $error ))
{
    $storyName = getStringValue( $error, $fatal, "StoryName" );
    $siteName  = getStringValue( $error, $fatal, "SiteName"  );
    $storyHome = getStringValue( $error, $fatal, "StoryHome" );
    $siteHome  = getStringValue( $error, $fatal, "SiteHome"  );
}

if ( ! empty( $error ))
{
    displayError( $error, $fatal );
}

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Story Tree</TITLE>
</HEAD><BODY>

<CENTER>
<H1><?php echo( $storyName ); ?>: Story Tree</H1>
<H2>
    Color Key for Child Episodes:
    <FONT COLOR="#008000">Created</FONT>,
    <FONT COLOR="#FF0000">Not Created</FONT>,
    <FONT COLOR="#0000FF">Back-Linked</FONT>
</H2>

<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>
</CENTER>

<?php

$curLevel    = 0;
$curEpisodes = array( $episode );

while ( count( $curEpisodes ) > 0 )
{

?>

<H2>Level: <?php echo( $curLevel ); ?>, Episodes: <?php echo( count( $curEpisodes )); ?></H2>
<TABLE BORDER="1" CELLSPACING="0" WIDTH="100%">
    <TR>
        <TH width="47%">Episode Number and Title</TH>
        <TH width="47%">Children of this Episode</TH>
        <TH width="6%">Parent</TH>
    </TR>
    <TR>

<?php

    sort( $curEpisodes, SORT_NUMERIC );
    $nextEpisodes = array();

    for ( $i = 0; $i < count( $curEpisodes ); $i++ )
    {
        $episode = $curEpisodes[ $i ];

        $result = mysql_query( "SELECT Parent, " .
                                      "Title " .
                                 "FROM Episode " .
                                "WHERE EpisodeID = " . $episode );

        if ( ! $result )
        {
            echo( "Problem retrieving episode from database." );
            exit;
        }

        $row = mysql_fetch_row( $result );

        if ( ! $row )
        {
            echo( "Problem fetching episode row from database." );
            exit;
        }

        $parent = $row[ 0 ];
        $title  = $row[ 1 ];

        $result = mysql_query( "SELECT TargetEpisodeID, " .
                                      "IsCreated, " .
                                      "IsBackLink " .
                                 "FROM Link " .
                                "WHERE SourceEpisodeID = " . $episode . " " .
                                "ORDER BY LinkID" );

        if ( ! $result )
        {
            echo( "Problem retrieving children from database." );
            exit;
        }

        $children = "";

        for ( $j = 0; $j < mysql_num_rows( $result ); $j++ )
        {
            $row = mysql_fetch_row( $result );

            $target     = $row[ 0 ];
            $isCreated  = $row[ 1 ];
            $isBackLink = $row[ 2 ];

            if ( $isBackLink == "Y" )
            {
                $color = "#0000FF";
            }
            else if ( $isCreated == "Y" )
            {
                $color = "#008000";
                array_push( $nextEpisodes, $target );
            }
            else
            {
                $color = "#FF0000";
            }

            if ( $j != 0 )
            {
                $children .= ", ";
            }

            $children .= "<A HREF=\"read.php?episode=" . $target . "\"><FONT COLOR=\"" .
                         $color . "\">" . $target . "</FONT></A>";
        }

?>

    <TR>
        <TD>
<A HREF="story-tree.php?episode=<?php echo( $episode ); ?>">View Tree</A> -
<A HREF="read.php?episode=<?php echo( $episode ); ?>">
    <?php echo( $episode ); ?> : <?php echo( $title ); ?>
</A>
        </TD>
        <TD><?php echo( empty( $children ) ? "&nbsp;" : $children ); ?></TD>
        <TD><A HREF="read.php?episode=<?php echo( $parent ); ?>"><?php echo( $parent ); ?></A></TD>
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

<?php require( "footer.php" ); ?>

</BODY></HTML>
