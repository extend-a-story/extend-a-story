<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2023 Jeffrey J. Weston <jjweston@gmail.com>


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

//$dbStatement = Util::getDbConnection()->prepare( "SELECT * FROM Episode WHERE 1=2" );

$dbStatement = Util::getDbConnection()->prepare(
        "SELECT COUNT( * ) FROM Episode WHERE Status = 2 OR Status = 3" );
$dbStatement->execute();
$row = $dbStatement->fetch( PDO::FETCH_NUM );

if ( !$row )
{
    throw new StoryException( "Problem fetching created episode count row from the database." );
}

$created = $row[ 0 ];

$dbStatement = Util::getDbConnection()->prepare(
        "SELECT COUNT( * ) FROM Episode WHERE Status = 0 OR Status = 1" );
$dbStatement->execute();
$row = $dbStatement->fetch( PDO::FETCH_NUM );

if ( !$row )
{
    throw new StoryException( "Problem fetching empty episode count row from the database." );
}

$empty = $row[ 0 ];

$dbStatement = Util::getDbConnection()->prepare( "SELECT COUNT( * ) FROM Episode" );
$dbStatement->execute();
$row = $dbStatement->fetch( PDO::FETCH_NUM );

if ( !$row )
{
    throw new StoryException( "Problem fetching episode count row from the database." );
}

$count = $row[ 0 ];

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Statistics</TITLE>
</HEAD><BODY>

<CENTER>
<H1><?php echo( $storyName ); ?>: Statistics</H1>
<H2>Created Episodes: <?php echo( $created ); ?></H2>
<H2>Empty Episodes:   <?php echo( $empty   ); ?></H2>
<H2>Total Episodes:   <?php echo( $count   ); ?></H2>

<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>
