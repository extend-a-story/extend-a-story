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

use \Extend_A_Story\HtmlElements\InputField;
use \Extend_A_Story\HtmlElements\RawText;
use \Extend_A_Story\HtmlElements\UnorderedList;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class AdminAccountPage extends InstallPage
{
    public static function validatePage()
    {
        $result = AdminAccountPage::validatePreviousPage();
        if ( isset( $result )) return $result;

        $adminLoginName   = Util::getStringParamDefault( $_POST, "adminLoginName",   "" );
        $adminDisplayName = Util::getStringParamDefault( $_POST, "adminDisplayName", "" );
        $adminPassword1   = Util::getStringParamDefault( $_POST, "adminPassword1",   "" );
        $adminPassword2   = Util::getStringParamDefault( $_POST, "adminPassword2",   "" );

        $errors = array();

        if ( strlen( $adminLoginName ) == 0 )
        {
            $errors[] = new RawText( "Login name must be set." );
        }

        if ( strlen( $adminDisplayName ) == 0 )
        {
            $errors[] = new RawText( "Display name must be set." );
        }

        if (( strlen( $adminPassword1 ) == 0 ) && ( strlen( $adminPassword2 ) == 0 ))
        {
            $errors[] = new RawText( "Password must be set." );
        }

        if ( $adminPassword1 != $adminPassword2 )
        {
            $errors[] = new RawText( "Paswords do not match." );
        }

        if ( count( $errors ) > 0 )
        {
            return new AdminAccountPage( new UnorderedList( $errors ));
        }

        return null;
    }

    private static function validatePreviousPage()
    {
        $task = Util::getStringParam( $_POST, "task" );
        if ( $task === "install" )
        {
            $result = DataLossWarningPage::validatePage();
            if ( isset( $result )) return $result;
        }
        else if ( $task === "upgrade" )
        {
            $result = VersionConfirmationPage::validatePage();
            if ( isset( $result )) return $result;
        }
        else throw new StoryException( "Unrecognized task." );

        return null;
    }

    private $adminLoginNameField;
    private $adminDisplayNameField;
    private $adminPassword1Field;
    private $adminPassword2Field;

    public function __construct( $error = null )
    {
        parent::__construct( $error );
    }

    public function validate()
    {
        $result = AdminAccountPage::validatePreviousPage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getNextPage()
    {
        $task = Util::getStringParam( $_POST, "task" );
        if ( $task === "install" )
        {
            $allowDataLoss = Util::getStringParamDefault( $_POST, "allowDataLoss", null );
            if (( isset( $this->backButton )) and ( isset( $allowDataLoss ))) return new DataLossWarningPage();
            if (( isset( $this->backButton )) and ( !isset( $allowDataLoss ))) return new SelectTaskPage();
            if ( isset( $this->continueButton )) return new StorySettingsPage();
        }
        else if ( $task === "upgrade" )
        {
            if ( isset( $this->backButton )) return new VersionConfirmationPage();
            if ( isset( $this->continueButton )) return new StorySettingsPage();
        }
        else throw new StoryException( "Unrecognized task." );

        throw new StoryException( "Unrecognized navigation from admin account page." );
    }

    protected function getSubtitle()
    {
        return "Administrator Account";
    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton",
                      "adminLoginName", "adminDisplayName", "adminPassword1", "adminPassword2" );
    }

    protected function preRender()
    {
        $adminLoginName   = Util::getStringParamDefault( $_POST, "adminLoginName",   "" );
        $adminDisplayName = Util::getStringParamDefault( $_POST, "adminDisplayName", "" );
        $adminPassword1   = Util::getStringParamDefault( $_POST, "adminPassword1",   "" );
        $adminPassword2   = Util::getStringParamDefault( $_POST, "adminPassword2",   "" );

        $this->adminLoginNameField = new InputField(
                "adminLoginName", "Login Name", "text", $adminLoginName,
                "This is the login name for the initial administrator account for your story." );

        $this->adminDisplayNameField = new InputField(
                "adminDisplayName", "Display Name", "text", $adminDisplayName,
                "This is the display name for the initial administrator account for your story. This name will be " .
                "publicly displayed on any moderation activity performed by that account in your story." );

        $this->adminPassword1Field = new InputField(
                "adminPassword1", "Pasword", "password", $adminPassword1,
                "This is the password for the initial administrator account for your story." );

        $this->adminPassword2Field = new InputField(
                "adminPassword2", "Confirm Password", "password", $adminPassword2,
                "Please confirm the password for the initial administrator account for your story." );
    }

    protected function renderMain()
    {

?>

<p>
    Create your administrator account.
</p>

<?php

        $this->adminLoginNameField->render();
        $this->adminDisplayNameField->render();
        $this->adminPassword1Field->render();
        $this->adminPassword2Field->render();

?>

<div class="submit">
    <input type="hidden" name="pageName" value="AdminAccount">
    <input type="submit" name="backButton" value="Back">
    <input type="submit" name="continueButton" value="Continue">
</div>

<?php

    }
}

?>
