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

$method = "";
$text   = "";
$days   = 0;

if ( isset( $_POST[ "method" ] ))
{
    $method = $_POST[ "method" ];
}

if ( isset( $_POST[ "text" ] ))
{
    $text = $_POST[ "text" ];
}

if ( isset( $_POST[ "days" ] ))
{
    $days = (int) $_POST[ "days" ];
}

$error = "";
$fatal = false;

if (( $method != "title"      ) &&
    ( $method != "text"       ) &&
    ( $method != "author"     ) &&
    ( $method != "time"       ) &&
    ( $method != "extendable" ) &&
    ( $method != "linkable"   ) &&
    ( $method != "days"       ))
{
    $error .= "The specified search method is not supported.<BR>";
    $fatal = true;
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
    if ( $method == "title" )
    {
        $whereClause = "Title LIKE '%" . mysqli_real_escape_string( $mysqli, $text ) . "%' " .
                   "AND ( Status = 2 OR Status = 3 )";
    }

    if ( $method == "text" )
    {
        $whereClause = "Text LIKE '%" . mysqli_real_escape_string( $mysqli, $text ) . "%' " .
                   "AND ( Status = 2 OR Status = 3 )";
    }

    if ( $method == "author" )
    {
        $whereClause = "AuthorName LIKE '%" . mysqli_real_escape_string( $mysqli, $text ) . "%' " .
                   "AND ( Status = 2 OR Status = 3 )";
    }

    if ( $method == "time" )
    {
        $whereClause = "CreationDate LIKE '%" . mysqli_real_escape_string( $mysqli, $text ) . "%' " .
                   "AND ( Status = 2 OR Status = 3 )";
    }

    if ( $method == "extendable" )
    {
        $whereClause = "IsExtendable = 'Y' AND ( Status = 2 OR Status = 3 )";
    }

    if ( $method == "linkable" )
    {
        $whereClause = "IsLinkable = 'Y' AND ( Status = 2 OR Status = 3 )";
    }

    if ( $method == "days" )
    {
        $whereClause = "CreationTimestamp > SUBDATE( NOW(), INTERVAL " . $days . " DAY ) " .
                   "AND ( Status = 2 OR Status = 3 )";
    }

    $result = mysqli_query( $mysqli,
                            "SELECT EpisodeID, " .
                                   "Title, " .
                                   "AuthorName " .
                              "FROM Episode " .
                             "WHERE " . $whereClause . " " .
                             "ORDER BY EpisodeID" );

    if ( ! $result )
    {
        $error .= "Problem retrieving the search results from the database.<BR>";
        $fatal = true;
    }
}

if ( ! empty( $error ))
{
    displayError( $error, $fatal );
}

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Search Results</TITLE>
</HEAD><BODY>

<CENTER>
<H1><?php echo( $storyName ); ?>: Search Results</H1>
</CENTER>

<TABLE>
    <TR>
        <TD><B>#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</B></TD>
        <TD><B>Episode Number and Title</B></TD>
        <TD><B>Author Name</B></TD>
    </TR>

<?php

for ( $i = 0; $i < mysqli_num_rows( $result ); $i++ )
{
    $row = mysqli_fetch_row( $result );

    $displayedTitle      = htmlentities( $row[ 1 ] );
    $displayedAuthorName = htmlentities( $row[ 2 ] );

?>

    <TR>
        <TD><?php echo( $i + 1 ); ?></TD>
        <TD>
            <A HREF="read.php?episode=<?php echo( $row[ 0 ] ); ?>">
                <?php echo( $row[ 0 ] ); ?> - <?php echo( $displayedTitle ); ?>
            </A>
        </TD>
        <TD><?php echo( $displayedAuthorName ); ?></TD>
    </TR>

<?php

}

?>

</TABLE>
<P>
<A HREF="search.php">Search Again</A>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

<?php require( "footer.php" ); ?>

</BODY></HTML>
