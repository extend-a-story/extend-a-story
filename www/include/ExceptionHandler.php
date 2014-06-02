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

function handleException( $exception )
{
    if ( $exception instanceof StoryException )
    {
        $exception->handle();
    }
    else
    {
        $content = "<p>" .
                       "An error has occurred. Please try again later. " .
                       "Contact the site administrator if this problem persists." .
                   "</p>";

        $simplePage = new \pages\SimplePage( "Error", null, $content, null, null );
        $simplePage->render();
    }
}

set_exception_handler( "handleException" );

?>
