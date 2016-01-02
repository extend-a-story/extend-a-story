<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2016 Jeffrey J. Weston


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

$dbStatement = Util::getDbConnection()->prepare(
        "SELECT Link.SourceEpisodeID, " .
               "Episode.Title " .
          "FROM Link, " .
               "Episode " .
         "WHERE Link.SourceEpisodeID = Episode.EpisodeID " .
           "AND Link.TargetEpisodeID = :episode " .
         "ORDER BY Episode.EpisodeID" );

$dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
$dbStatement->execute();
$rows = $dbStatement->fetchAll( PDO::FETCH_NUM );

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Back Link Trace for Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1><?php echo( $storyName ); ?>: Back Link Trace for Episode <?php echo( $episode ); ?></H1>

<TABLE>
    <TR>
        <TD>
            <OL>

<?php

for ( $i = 0; $i < count( $rows ); $i++ )
{
    $row = $rows[ $i ];

    $source = $row[ 0 ];
    $title  = $row[ 1 ];

    $title = htmlentities( $title );

?>

                <LI>
                    <A HREF="read.php?episode=<?php echo( $source ); ?>">
                        <?php echo( $title ); ?>
                    </A>
                </LI>

<?php

}

?>

            </OL>
        </TD>
    </TR>
</TABLE>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>
