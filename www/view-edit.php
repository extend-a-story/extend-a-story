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

require(  __DIR__ . "/include/Extend-A-Story.php" );

require( "ExtendAStory.php" );

$episodeEditLogID = 1;

if ( isset( $_GET[ "episodeEditLogID" ] ))
{
    $episodeEditLogID = (int) $_GET[ "episodeEditLogID" ];
}

Util::connectToDatabase();
Util::getSessionAndUserIDs( $sessionID, $userID );

$storyName = Util::getStringValue( "StoryName" );
$siteName  = Util::getStringValue( "SiteName"  );
$storyHome = Util::getStringValue( "StoryHome" );
$siteHome  = Util::getStringValue( "SiteHome"  );

$result = mysql_query( "SELECT EpisodeID, " .
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
                              "EditDate " .
                         "FROM EpisodeEditLog " .
                        "WHERE EpisodeEditLogID = " . $episodeEditLogID );

if ( ! $result )
{
    throw new HardStoryException( "Problem retrieving episode edit log record from the database." );
}

$row = mysql_fetch_row( $result );

if ( ! $row )
{

?>

<HTML><HEAD>
<TITLE>Story Error - Episode Edit Log <?php echo( $episodeEditLogID ); ?> Not Found</TITLE>
</HEAD><BODY>

<CENTER>

<H1>Story Error</H1>
<H2>Story Error - Episode Edit Log <?php echo( $episodeEditLogID ); ?> Not Found</H2>
<A HREF="read.php">In the beginning...</A>

</CENTER>

<?php require( "footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

$episode      = $row[ 0  ];
$scheme       = $row[ 1  ];
$image        = $row[ 2  ];
$isLinkable   = $row[ 3  ];
$isExtendable = $row[ 4  ];
$authorMailto = $row[ 5  ];
$authorNotify = $row[ 6  ];
$title        = $row[ 7  ];
$text         = $row[ 8  ];
$authorName   = $row[ 9  ];
$authorEmail  = $row[ 10 ];
$editDate     = $row[ 11 ];

$canEdit = Util::canEditEpisode( $sessionID, $userID, $episode );

if ( ! $canEdit )
{

?>

<HTML><HEAD>
<TITLE>Edit Log - Error</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Edit Log - Error</H1>

<TABLE WIDTH="500">
    <TR>
        <TD>
You do not have permission to view this edit log.
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

$result = mysql_query( "SELECT Parent FROM Episode WHERE EpisodeID = " . $episode );

if ( ! $result )
{
    throw new HardStoryException( "Problem retrieving the episode from the database." );
}

    $row = mysql_fetch_row( $result );

if ( ! $row )
{
    throw new HardStoryException( "Problem fetching episode row from the database." );
}

$parent = $row[ 0 ];

$result = mysql_query( "SELECT COUNT( * ) FROM Link WHERE TargetEpisodeID = " . $episode );

if ( ! $result )
{
    throw new HardStoryException( "Problem retrieving link count from the database." );
}

    $row = mysql_fetch_row( $result );

if ( ! $row )
{
    throw new HardStoryException( "Problem fetching link count row from the database." );
}

$linkCount = (int) $row[ 0 ];

$title      = htmlentities( $title );
$text       = htmlentities( $text  );
$authorName = htmlentities( $authorName );

$text        = strtr( $text,        Util::getEpisodeBodyTranslation()  );
$authorEmail = strtr( $authorEmail, Util::getEmailAddressTranslation() );

$result = mysql_query( "SELECT bgcolor, " .
                              "text, " .
                              "link, " .
                              "vlink, " .
                              "alink, " .
                              "background, " .
                              "UncreatedLink, " .
                              "CreatedLink, " .
                              "BackLinkedLink " .
                         "FROM Scheme " .
                        "WHERE SchemeID = " . $scheme );

if ( ! $result )
{
    throw new HardStoryException( "Problem retrieving the scheme from the database." );
}

$row = mysql_fetch_row( $result );

if ( ! $row )
{
    throw new HardStoryException( "Problem fetching scheme row from the database." );
}

$bgcolorColor   = $row[ 0 ];
$textColor      = $row[ 1 ];
$linkColor      = $row[ 2 ];
$vlinkColor     = $row[ 3 ];
$alinkColor     = $row[ 4 ];
$background     = $row[ 5 ];
$uncreatedLink  = $row[ 6 ];
$createdLink    = $row[ 7 ];
$backLinkedLink = $row[ 8 ];

$body = "<BODY BGCOLOR=\"" . $bgcolorColor . "\" " .
              "TEXT=\""    . $textColor    . "\" " .
              "LINK=\""    . $linkColor    . "\" " .
              "VLINK=\""   . $vlinkColor   . "\" " .
              "ALINK=\""   . $alinkColor   . "\""  .
              ( empty( $background ) ? ">" :
                                       " BACKGROUND=\"" . $background . "\">" );

if ( $image != 0 )
{
    $result = mysql_query( "SELECT ImageURL FROM Image WHERE ImageID = " . $image );

    if ( ! $result )
    {
        throw new HardStoryException( "Problem retrieving the image from the database." );
    }

    $row = mysql_fetch_row( $result );

    if ( ! $row )
    {
        throw new HardStoryException( "Problem fetching image row from the database." );
    }

    $image = $row[ 0 ];
}

$result = mysql_query( "SELECT TargetEpisodeID, " .
                              "IsBackLink, " .
                              "Description " .
                         "FROM LinkEditLog " .
                        "WHERE EpisodeEditLogID = " . $episodeEditLogID . " " .
                        "ORDER BY LinkEditLogID" );

if ( ! $result )
{
    throw new HardStoryException( "Problem retrieving link edit log from database." );
}

?>

<HTML><HEAD>
<TITLE>
    <?php echo( $storyName ); ?>: <?php echo( $title ); ?>
    [Episode <?php echo( $episode ); ?>] - Edit Log
</TITLE>
</HEAD><?php echo( $body ); ?>

<CENTER>
<H1><?php echo( $title ); ?></H1>
<H2><?php echo( $storyName ); ?> - Episode <?php echo( $episode ); ?> - Edit Log</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
<?php echo( $text ); ?>

<?php

if ( ! empty( $image ))
{

?>

<P>
<CENTER>
<IMG SRC="<?php echo( $image ); ?>">
</CENTER>

<?php

}

?>

<P>
<OL>

<?php

for ( $i = 0; $i < mysql_num_rows( $result ); $i++ )
{
    $row = mysql_fetch_row( $result );

    $description = $row[ 2 ];
    $description = htmlentities( $description );
    $description = strtr( $description, Util::getOptionTranslation() );

    if ( $row[ 1 ] == "Y" )
    {
        $image = $backLinkedLink;
    }
    else
    {
        $image = $uncreatedLink;
    }

?>

<LI>
    <IMG SRC="<?php echo( $image ); ?>">
    <A HREF="read.php?episode=<?php echo( $row[ 0 ] ); ?>"><?php echo( $description ); ?></A>
</LI>

<?php

}

?>

</OL>

<?php

if ( $isExtendable == "Y" )
{

?>

<P>
<A HREF="create.php?episode=<?php echo( $episode ); ?>&command=Extend">Add New Option</A>

<?php

}

if ( $episode != 1 )
{

?>

<P>
<A HREF="read.php?episode=<?php echo( $parent ); ?>">Go Back</A>

<?php

}

if (( $linkCount > 1 ) || (( $linkCount > 0 ) && ( $episode == 1  )))
{

?>

<P>
<A HREF="link-trace.php?episode=<?php echo( $episode ); ?>">

<?php

    if ( $linkCount == 1 )
    {

?>

    Display Link to this Episode

<?php

    }
    else
    {

?>

    Display All <?php echo( $linkCount ); ?> Links to this Episode


<?php

    }

?>
</A>

<?php

}

?>

        </TD>
    </TR>
</TABLE>

<HR>

Author Name: <?php echo( $authorName ); ?>
<P>
Author Email: <?php echo( $authorEmail ); ?>
<P>
Author Mailto: <?php echo( $authorMailto ); ?>
<P>
Author Notify: <?php echo( $authorNotify ); ?>
<P>
Is Linkable: <?php echo( $isLinkable ); ?>
<P>
Is Extendable: <?php echo( $isExtendable ); ?>
<P>
Edit Date: <?php echo( $editDate ); ?>
<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">View the Current Version</A>
<P>
<A HREF="list-edits.php?episode=<?php echo( $episode ); ?>">Go Back to Edit List</A>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

<?php require( "footer.php" ); ?>

</BODY></HTML>
