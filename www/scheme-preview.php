<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2024 Jeffrey J. Weston <jjweston@gmail.com>


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

$scheme = Util::getIntParamDefault( $_POST, "scheme", null );

if ( !isset( $scheme ))
{
    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT SchemeID FROM Scheme ORDER BY SchemeID" );

    $dbStatement->execute();
    $row = $dbStatement->fetch( PDO::FETCH_NUM );

    if ( !$row )
    {
        throw new StoryException( "Unable to fetch the lowest scheme ID." );
    }

    $scheme = $row[ 0 ];
}

$dbStatement = Util::getDbConnection()->prepare(
        "SELECT SchemeName, " .
               "bgcolor, " .
               "text, " .
               "link, " .
               "vlink, " .
               "alink, " .
               "background, " .
               "UncreatedLink, " .
               "CreatedLink, " .
               "BackLinkedLink " .
          "FROM Scheme " .
         "WHERE SchemeID = :scheme" );

$dbStatement->bindParam( ":scheme", $scheme, PDO::PARAM_INT );
$dbStatement->execute();
$row = $dbStatement->fetch( PDO::FETCH_NUM );

if ( !$row )
{
    throw new StoryException( "Problem fetching scheme row from the database." );
}

$schemeName     = $row[ 0 ];
$bgcolor        = $row[ 1 ];
$text           = $row[ 2 ];
$link           = $row[ 3 ];
$vlink          = $row[ 4 ];
$alink          = $row[ 5 ];
$background     = $row[ 6 ];
$uncreatedLink  = $row[ 7 ];
$createdLink    = $row[ 8 ];
$backLinkedLink = $row[ 9 ];

$body = "<BODY BGCOLOR=\"" . $bgcolor . "\" " .
              "TEXT=\""    . $text    . "\" " .
              "LINK=\""    . $link    . "\" " .
              "VLINK=\""   . $vlink   . "\" " .
              "ALINK=\""   . $alink   . "\""  .
              ( empty( $background ) ? ">" :
                                       " BACKGROUND=\"" . $background . "\">" );

$dbStatement = Util::getDbConnection()->prepare( "SELECT SchemeID, SchemeName FROM Scheme" );
$dbStatement->execute();
$rows = $dbStatement->fetchAll( PDO::FETCH_NUM );

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Scheme Preview</TITLE>
</HEAD><?php echo( $body ); ?>

<CENTER>
<H1><?php echo( $storyName ); ?>: Scheme Preview</H1>
<H2>Now Previewing: <?php echo( $schemeName ); ?></H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
This special page allows you to preview the color schemes available to you
when creating episodes. Since many of the schemes would interfere with the
creation interface, this preview is loaded in a seperate window.
<P>
<OL>
<LI>
    <IMG SRC="<?php echo( $uncreatedLink  ); ?>">
    <A HREF="#">This is an example of an uncreated link.</A>
</LI>
<LI>
    <IMG SRC="<?php echo( $createdLink    ); ?>">
    <A HREF="#">This is an example of a created link.</A>
</LI>
<LI>
    <IMG SRC="<?php echo( $backLinkedLink ); ?>">
    <A HREF="#">This is an example of a back linked link.</A>
</LI>
</OL>
<P>
<FORM METHOD="POST">
Select another scheme to preview:<BR>
<SELECT NAME="scheme">

<?php

for ( $i = 0; $i < count( $rows ); $i++ )
{
    $row = $rows[ $i ];
    $selected = ( $scheme == $row[ 0 ] ) ? " SELECTED" : "";

?>

<OPTION VALUE="<?php echo( $row[ 0 ] ); ?>"<?php echo( $selected ); ?>>
    <?php echo( $row[ 1 ] );?>
</OPTION>

<?php

}

?>

</SELECT>
<INPUT TYPE="submit" VALUE="Go">
</FORM>
        </TD>
    </TR>
</TABLE>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>
