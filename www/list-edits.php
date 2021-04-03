<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2021 Jeffrey J. Weston <jjweston@gmail.com>


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

use \Extend_A_Story\Util;

Util::getSessionAndUserIDs( $sessionID, $userID );

$storyName = Util::getStringValue( "StoryName" );
$siteName  = Util::getStringValue( "SiteName"  );
$storyHome = Util::getStringValue( "StoryHome" );
$siteHome  = Util::getStringValue( "SiteHome"  );

$episode = Util::getIntParam( $_GET, "episode" );

if ( !Util::canEditEpisode( $sessionID, $userID, $episode ))
{

?>

<HTML><HEAD>
<TITLE>List Edits - Error</TITLE>
</HEAD><BODY>

<CENTER>
<H1>List Edits - Error</H1>

<TABLE WIDTH="500">
    <TR>
        <TD>
You do not have permission to view the edits for this episode.
<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
        </TD>
    </TR>
</TABLE>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

$dbStatement = Util::getDbConnection()->prepare(
        "SELECT EpisodeEditLogID, " .
               "EditDate, " .
               "EditLogEntry " .
          "FROM EpisodeEditLog " .
         "WHERE EpisodeID = :episode " .
         "ORDER BY EpisodeEditLogID" );

$dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
$dbStatement->execute();
$rows = $dbStatement->fetchAll( PDO::FETCH_NUM );

?>

<HTML><HEAD>
<TITLE>Viewing Edits for Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1>Viewing Edits for Episode <?php echo( $episode ); ?></H1>

Clicking on a <I>View Edit</I> link views the episode as it was <B>before</B> that edit took place.
<P>
<TABLE>
    <TR>
        <TH>#</TH>
        <TH>Date</TH>
        <TH>Log Entry</TH>
    </TR>

<?php

for ( $i = 0; $i < count( $rows ); $i++ )
{
    $row = $rows[ $i ];

?>

    <TR>
        <TD>
            <A HREF="view-edit.php?episodeEditLogID=<?php echo( $row[ 0 ] ); ?>">
                View Edit #<?php echo( $i + 1 ); ?>
            </A>
        </TD>
        <TD>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo( $row[ 1 ] ); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </TD>
        <TD><?php echo( $row[ 2 ] ); ?></TD>
    </TR>

<?php

}

?>

</TABLE>
<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>
