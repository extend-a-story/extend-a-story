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
                            "SELECT EpisodeID " .
                              "FROM Episode " .
                             "WHERE Status = 1 " .
                             "ORDER BY EpisodeID" );

    if ( ! $result )
    {
        $error .= "Problem retrieving locked episode list from the database.<BR>";
        $fatal = true;
    }
}

if ( ! empty( $error ))
{
    displayError( $error, $fatal );
}

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Locked Episodes</TITLE>
</HEAD><BODY>

<CENTER>
<H1><?php echo( $storyName ); ?>: Locked Episodes</H1>
</CENTER>

<OL>

<?php

for ( $i = 0; $i < mysqli_num_rows( $result ); $i++ )
{
    $row = mysqli_fetch_row( $result );

?>

    <LI><A HREF="read.php?episode=<?php echo( $row[ 0 ] ); ?>"><?php echo( $row[ 0 ] ); ?></A></LI>

<?php

}

?>

</OL>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

<?php require( "footer.php" ); ?>

</BODY></HTML>
