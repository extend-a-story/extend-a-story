<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2016 Jeffrey J. Weston


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

namespace Extend_A_Story\Pages\Install;

abstract class InstallPage
{
    private $error;

    public function __construct( $error = null )
    {
        $this->error = $error;
    }

    public function validate()
    {
        return $this;
    }

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
        <script type="text/javascript" src="script.js"></script>
        <title><?php echo( $title ); ?> - <?php echo( $this->getSubtitle() ); ?></title>
    </head>
    <body>
        <form action="install.php" method="post">

            <div class="navigation">
                <ul>
                    <li>Navigation</li>
                    <li>Links</li>
                    <li>Go</li>
                    <li>Here</li>
                </ul>
            </div>

            <div class="content">

<?php

$fields = $this->getFields();
$keys = array_keys( $_POST );

for ( $i = 0; $i < count( $keys ); $i++ )
{
    $key = $keys[ $i ];

    if ( ! in_array( $key, $fields ))
    {
        $value = $_POST[ $key ];

?>

                <input type="hidden"
                       name="<?php echo( htmlentities( $key )); ?>"
                       value="<?php echo( htmlentities( $value )); ?>" />

<?php

    }
}

?>

                <h1><?php echo( $title ); ?></h1>

                <div class="main">

                    <h2><?php echo( $this->getSubtitle() ); ?></h2>

<?php

        if ( isset( $this->error ))
        {

?>

                    <div class="error"><?php $this->error->render(); ?></div>

<?php

        }

        $this->renderMain();

?>

                </div>
            </div>
        </form>

        <?php require( __DIR__ . "/../../../../config/Footer.php" ); ?>

    </body>
</html>

<?php

    }

    protected abstract function getSubtitle();

    protected abstract function renderMain();

    protected abstract function getFields();
}

?>
