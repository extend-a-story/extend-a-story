<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2021 Jeffrey J. Weston <jjweston@gmail.com>


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

use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

abstract class InstallPage
{
    public static function getPage()
    {
        $pageName = Util::getStringParamDefault( $_POST, "pageName", null );

        if ( isset( $pageName ))
        {
            switch ( $pageName )
            {
                case "Authorization"       : $page = new AuthorizationPage();       break;
                case "DisableStory"        : $page = new DisableStoryPage();        break;
                case "Start"               : $page = new StartPage();               break;
                case "DatabaseConnection"  : $page = new DatabaseConnectionPage();  break;
                case "SelectTask"          : $page = new SelectTaskPage();          break;
                case "DataLossWarning"     : $page = new DataLossWarningPage();     break;
                case "AdminAccount"        : $page = new AdminAccountPage();        break;
                case "StorySettings"       : $page = new StorySettingsPage();       break;
                case "VersionConfirmation" : $page = new VersionConfirmationPage(); break;
                case "Confirmation"        : $page = new ConfirmationPage();        break;
                default : throw new StoryException( "Unrecognized page." );
            }

            $page = $page->getNextPage();
        }
        else
        {
            $page = new StartPage();
        }

        return $page;
    }

    protected $backButton;
    protected $continueButton;
    protected $installToken;

    private $error;
    private $installTokenPost;
    private $installTokenCookie;

    public function __construct( $error = null )
    {
        $this->error = $error;

        $this->backButton         = Util::getStringParamDefault( $_POST,   "backButton",     null );
        $this->continueButton     = Util::getStringParamDefault( $_POST,   "continueButton", null );
        $this->installTokenPost   = Util::getStringParamDefault( $_POST,   "installToken",   null );
        $this->installTokenCookie = Util::getStringParamDefault( $_COOKIE, "installToken",   null );
    }

    public function validate()
    {
        throw new StoryException( "This function is not implemented." );
    }

    public function render()
    {
        $this->preRender();
        $this->handleInstallToken();
        global $version;
        $title = "Extend-A-Story " . $version . " Installation";

?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" href="style.css">
        <script src="script.js"></script>
        <title><?php echo( htmlentities( $title )); ?> - <?php echo( htmlentities( $this->getSubtitle() )); ?></title>
    </head>
    <body>
        <form action="install.php" method="post">

            <div class="navigation">
                <ul>
                    <li>Extend-A-Story:</li>
                    <li>
                        <ul>
                            <li>
                                <a href="http://www.sir-toby.com/phpbb/viewforum.php?f=3">
                                    Forum
                                </a>
                            </li>
                            <li>
                                <a href="https://github.com/extend-a-story/extend-a-story">
                                    GitHub Project
                                </a>
                            </li>
                            <li>
                                <a href="http://www.sir-toby.com/extend-a-story/">
                                    Home Page
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="content">

<?php

        // add hidden input fields for all fields that are not managed by this page
        $fields = $this->getFields();
        $keys = array_keys( $_POST );
        for ( $i = 0; $i < count( $keys ); $i++ )
        {
            $key = $keys[ $i ];

            if ( !in_array( $key, $fields ))
            {
                $value = $_POST[ $key ];
                $this->renderHiddenInput( $key, $value );
            }
        }

        // add hidden input for the install token, if it doesn't exist
        if ( !in_array( "installToken", $keys ))
        {
            $this->renderHiddenInput( "installToken", $this->installToken );
        }

?>

                <h1><?php echo( htmlentities( $title )); ?></h1>

                <div class="main">

                    <h2><?php echo( htmlentities( $this->getSubtitle() )); ?></h2>

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

    protected abstract function getNextPage();

    protected abstract function getSubtitle();

    protected abstract function getFields();

    protected function preRender()
    {
    }

    protected abstract function renderMain();

    private function handleInstallToken()
    {
        if ( isset( $this->installTokenPost )) $this->installToken = $this->installTokenPost;
        else if ( isset( $this->installTokenCookie )) $this->installToken = $this->installTokenCookie;
        else $this->installToken = $this->generateInstallToken();

        $oneWeek = 60 * 60 * 24 * 7; // number of seconds in one week
        setcookie( "installToken", $this->installToken, time() + $oneWeek );
    }

    private function generateInstallToken()
    {
        $bytes = random_bytes( 16 );
        $result = "";

        for ( $i = 0; $i < strlen( $bytes ); $i++ )
        {
            $result .= sprintf( "%02x", ord( $bytes[ $i ] ));
        }

        return $result;
    }

    private function renderHiddenInput( $name, $value )
    {

?>

                <input type="hidden"
                       name="<?php echo( htmlentities( $name )); ?>"
                       value="<?php echo( htmlentities( $value )); ?>">

<?php

    }
}

?>
