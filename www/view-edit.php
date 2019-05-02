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

$episodeEditLogID = 1;

if ( isset( $_GET[ "episodeEditLogID" ] ))
{
    $episodeEditLogID = (int) $_GET[ "episodeEditLogID" ];
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
                            "SELECT EpisodeID, " .
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
        $error .= "Problem retrieving episode edit log record from the database.<BR>";
        $fatal = true;
    }
    else
    {
        $row = mysqli_fetch_row( $result );

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
    }
}

$canEdit = canEditEpisode( $sessionID, $userID, $episode );

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

if ( empty( $error ))
{
    $result = mysqli_query( $mysqli, "SELECT Parent FROM Episode WHERE EpisodeID = " . $episode );

    if ( ! $result )
    {
        $error .= "Problem retrieving the episode from the database.<BR>";
        $fatal = true;
    }
    else
    {
        $row = mysqli_fetch_row( $result );

        if ( ! $row )
        {
            $error .= "Problem fetching episode row from the database.<BR>";
            $fatal = true;
        }
        else
        {
            $parent = $row[ 0 ];
        }
    }
}

if ( empty( $error ))
{
    $result = mysqli_query( $mysqli, "SELECT COUNT( * ) FROM Link WHERE TargetEpisodeID = " . $episode );

    if ( ! $result )
    {
        $error .= "Problem retrieving link count from the database.<BR>";
        $fatal = true;
    }
    else
    {
        $row = mysqli_fetch_row( $result );

        if ( ! $row )
        {
            $error .= "Problem fetching link count row from the database.<BR>";
            $fatal = true;
        }
        else
        {
            $linkCount = (int) $row[ 0 ];
        }
    }
}

$title      = htmlentities( $title );
$text       = htmlentities( $text  );
$authorName = htmlentities( $authorName );

$text        = strtr( $text,        getEpisodeBodyTranslationTable()  );
$authorEmail = strtr( $authorEmail, getEmailAddressTranslationTable() );

if ( empty( $error ))
{
    $result = mysqli_query( $mysqli,
                            "SELECT bgcolor, " .
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
        $error .= "Problem retrieving the scheme from the database.<BR>";
        $fatal = true;
    }
    else
    {
        $row = mysqli_fetch_row( $result );

        if ( ! $row )
        {
            $error .= "Problem fetching scheme row from the database.<BR>";
            $fatal = true;
        }
        else
        {
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
        }
    }
}

if (( empty( $error )) && ( $image != 0 ))
{
    $result = mysqli_query( $mysqli, "SELECT ImageURL FROM Image WHERE ImageID = " . $image );

    if ( ! $result )
    {
        $error .= "Problem retrieving the image from the database.<BR>";
        $fatal = true;
    }
    else
    {
        $row = mysqli_fetch_row( $result );

        if ( ! $row )
        {
            $error .= "Problem fetching image row from the database.<BR>";
            $fatal = true;
        }
        else
        {
            $image = $row[ 0 ];
        }
    }
}

if ( empty( $error ))
{
    $result = mysqli_query( $mysqli,
                            "SELECT TargetEpisodeID, " .
                                   "IsBackLink, " .
                                   "Description " .
                              "FROM LinkEditLog " .
                             "WHERE EpisodeEditLogID = " . $episodeEditLogID . " " .
                             "ORDER BY LinkEditLogID" );

    if ( ! $result )
    {
        $error .= "Problem retrieving link edit log from database.<BR>";
        $fatal = true;
    }
}

if ( ! empty( $error ))
{
    displayError( $error, $fatal );
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

for ( $i = 0; $i < mysqli_num_rows( $result ); $i++ )
{
    $row = mysqli_fetch_row( $result );

    $description = $row[ 2 ];
    $description = htmlentities( $description );
    $description = strtr( $description, getOptionTranslationTable() );

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
