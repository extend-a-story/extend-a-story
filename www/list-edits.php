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

if ( empty( $error ))
{
    $result = mysqli_query( $mysqli,
                            "SELECT EpisodeEditLogID, " .
                                   "EditDate, " .
                                   "EditLogEntry " .
                              "FROM EpisodeEditLog " .
                             "WHERE EpisodeID = " . $episode . " " .
                             "ORDER BY EpisodeEditLogID" );

    if ( ! $result )
    {
        $error .= "Problem retrieving edit list from the database.<BR>";
        $fatal = true;
    }
}

$canEdit = canEditEpisode( $sessionID, $userID, $episode );

if ( ! empty( $error ))
{
    displayError( $error, $fatal );
}

if ( ! $canEdit )
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

<?php require( "footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

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

for ( $i = 0; $i < mysqli_num_rows( $result ); $i++ )
{
    $row = mysqli_fetch_row( $result );

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

<?php require( "footer.php" ); ?>

</BODY></HTML>
