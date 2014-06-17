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

namespace pages\install;

class StartPage
{
    public function render()
    {
        global $version;
        $title = "Extend-A-Story " . $version . " Installation";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="style.css" />
        <title><?php echo( $title ); ?></title>
    </head>
    <body>
        <div class="navigation">
            <ul>
                <li>Navigation</li>
                <li>Links</li>
                <li>Go</li>
                <li>Here</li>
            </ul>
        </div>

        <div class="content">
            <h1><?php echo( $title ); ?></h1>

            <div class="main">
                <p>
                    This page will guide you through the Extend-A-Story installation.
                </p>
            </div>
        </div>

        <?php require( __DIR__ . "/../../../config/Footer.php" ); ?>

    </body>
</html>

<?php

    }
}

?>
