<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2018 Jeffrey J. Weston <jjweston@gmail.com>


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

use \Extend_A_Story\HtmlElements\RadioButton;
use \Extend_A_Story\HtmlElements\RawText;
use \Extend_A_Story\HtmlElements\UnorderedList;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class SelectTaskPage extends InstallPage
{
    private $installTaskRadioButton;
    private $upgradeTaskRadioButton;

    public function __construct( $error = null )
    {
        parent::__construct( $error );
    }

    public function validate()
    {
        $result = DatabaseConnectionPage::validatePage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getNextPage()
    {
        if ( isset( $this->backButton )) return new DatabaseConnectionPage();

        if ( isset( $this->continueButton ))
        {
            $task = Util::getStringParamDefault( $_POST, "task", null );
            if ( !isset( $task ))
            {
                $message = "You must select an installation task.";
                $error = new UnorderedList( [ new RawText( $message ) ] );
                return new SelectTaskPage( $error );
            }

            if ( $task === "install" )
            {
                $allowDataLoss = Util::getStringParamDefault( $_POST, "allowDataLoss", null );
                if ( isset( $allowDataLoss )) return new DataLossWarningPage();
                if ( !isset( $allowDataLoss )) return new AdminAccountPage();
            }

            if ( $task === "upgrade" ) return new UpgradeConfirmationPage();
        }

        throw new StoryException( "Unrecognized navigation from select task page." );
    }

    protected function getSubtitle()
    {
        return "Select Task";
    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton",
                      "task" );
    }

    protected function preRender()
    {
        $task = Util::getStringParamDefault( $_POST, "task", null );

        $this->installTaskRadioButton = new RadioButton( "task", "task-install", "install", ( $task === "install" ),
                                                         "Install New Database" );

        $this->upgradeTaskRadioButton = new RadioButton( "task", "task-upgrade", "upgrade", ( $task === "upgrade" ),
                                                         "Upgrade Existing Database" );
    }

    protected function renderMain()
    {

?>

<p>
    Which installation task would you like to perform?
</p>

<?php

        $this->installTaskRadioButton->render();
        $this->upgradeTaskRadioButton->render();

?>

<div class="submit">
    <input type="hidden" name="pageName" value="SelectTask">
    <input type="submit" name="backButton" value="Back">
    <input type="submit" name="continueButton" value="Continue">
</div>

<?php

    }
}

?>
