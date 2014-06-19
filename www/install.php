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

use \Extend_A_Story\Pages\Install\ConfirmationPage;
use \Extend_A_Story\Pages\Install\DatabaseConnectionPage;
use \Extend_A_Story\Pages\Install\StartPage;

use \Extend_A_Story\HardStoryException;
use \Extend_A_Story\Util;

$pageName       = Util::getStringParamDefault( $_POST, "pageName",       null );
$backButton     = Util::getStringParamDefault( $_POST, "backButton",     null );
$continueButton = Util::getStringParamDefault( $_POST, "continueButton", null );

if ( isset( $pageName ))
{
    if ( $pageName == "Start" )
    {
        if ( isset( $continueButton ))
        {
            $page = new DatabaseConnectionPage();
            $page->render();
            exit;
        }

        throw new HardStoryException( "Invalid state." );
    }

    if ( $pageName == "DatabaseConnection" )
    {
        if ( isset( $backButton ))
        {
            $page = new StartPage();
            $page->render();
            exit;
        }

        if ( isset( $continueButton ))
        {
            $page = new ConfirmationPage();
            $page->render();
            exit;
        }

        throw new HardStoryException( "Invalid state." );
    }

    if ( $pageName == "Confirmation" )
    {
        if ( isset( $backButton ))
        {
            $page = new DatabaseConnectionPage();
            $page->render();
            exit;
        }

        throw new HardStoryException( "Invalid state." );
    }

    throw new HardStoryException( "Invalid state." );
}

$page = new StartPage();
$page->render();
exit;

?>
