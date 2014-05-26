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

Util::getSessionAndUserIDs( $sessionID, $userID );

$storyName = Util::getStringValue( "StoryName" );
$siteName  = Util::getStringValue( "SiteName"  );
$storyHome = Util::getStringValue( "StoryHome" );
$siteHome  = Util::getStringValue( "SiteHome"  );

$method = Util::getStringParam(        $_POST, "method"     );
$text   = Util::getStringParamDefault( $_POST, "text",   "" );
$days   = Util::getIntParamDefault(    $_POST, "days",   0  );

$text = "%" . $text . "%";

$dbStatement;

$queryPart1 = "SELECT EpisodeID, " .
                     "Title, " .
                     "AuthorName " .
                "FROM Episode " .
               "WHERE ";

$queryPart2 =    "AND ( Status = 2 OR Status = 3 ) " .
               "ORDER BY EpisodeID";

if ( $method == "title" )
{
    $dbStatement = Util::getDbConnection()->prepare(
            $queryPart1 . "Title LIKE :text " . $queryPart2 );

    $dbStatement->bindParam( ":text", $text, PDO::PARAM_STR );
}
else if ( $method == "text" )
{
    $dbStatement = Util::getDbConnection()->prepare(
            $queryPart1 . "Text LIKE :text " . $queryPart2 );

    $dbStatement->bindParam( ":text", $text, PDO::PARAM_STR );
}
else if ( $method == "author" )
{
    $dbStatement = Util::getDbConnection()->prepare(
            $queryPart1 . "AuthorName LIKE :text " . $queryPart2 );

    $dbStatement->bindParam( ":text", $text, PDO::PARAM_STR );
}
else if ( $method == "time" )
{
    $dbStatement = Util::getDbConnection()->prepare(
            $queryPart1 . "CreationDate LIKE :text " . $queryPart2 );

    $dbStatement->bindParam( ":text", $text, PDO::PARAM_STR );
}
else if ( $method == "extendable" )
{
    $dbStatement = Util::getDbConnection()->prepare(
            $queryPart1 . "IsExtendable = 'Y' " . $queryPart2 );
}
else if ( $method == "linkable" )
{
    $dbStatement = Util::getDbConnection()->prepare(
            $queryPart1 . "IsLinkable = 'Y' " . $queryPart2 );
}
else if ( $method == "days" )
{
    $dbStatement = Util::getDbConnection()->prepare(
            $queryPart1 .
            "CreationTimestamp > SUBDATE( NOW(), INTERVAL :days DAY ) " .
            $queryPart2 );

    $dbStatement->bindParam( ":days", $days, PDO::PARAM_INT );
}
else
{
    throw new HardStoryException( "The specified search method is not supported." );
}

$dbStatement->execute();
$rows = $dbStatement->fetchAll( PDO::FETCH_NUM );

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

for ( $i = 0; $i < count( $rows ); $i++ )
{
    $row = $rows[ $i ];

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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>
