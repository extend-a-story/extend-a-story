<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2025 Jeffrey J. Weston <jjweston@gmail.com>


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

use \Extend_A_Story\Data\Episode;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

Util::getSessionAndUserIDs( $sessionID, $userID );

$storyName = Util::getStringValue( "StoryName" );
$siteName  = Util::getStringValue( "SiteName"  );
$storyHome = Util::getStringValue( "StoryHome" );
$siteHome  = Util::getStringValue( "SiteHome"  );

$createdCount = Episode::getCreatedCount();
$emptyCount   = Episode::getEmptyCount();
$totalCount   = Episode::getTotalCount();

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo( $storyName ); ?>: Statistics</title>
    </head>
    <body>

        <div style="text-align: center;">
            <h1><?php echo( $storyName ); ?>: Statistics</h1>
            <h2>Created Episodes: <?php echo( number_format( $createdCount )); ?></h2>
            <h2>Empty Episodes:   <?php echo( number_format( $emptyCount   )); ?></h2>
            <h2>Total Episodes:   <?php echo( number_format( $totalCount   )); ?></h2>

            <p><a HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</a></p>
            <p><a HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</a></p>
        </div>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

    </body>
</html>
