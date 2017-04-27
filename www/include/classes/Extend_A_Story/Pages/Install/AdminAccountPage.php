<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2017 Jeffrey J. Weston


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
use \Extend_A_Story\Util;

class AdminAccountPage extends InstallPage
{
    public static function validatePage()
    {
        $result = DatabaseConnectionPage::validatePage();
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

    public function __construct( $error = null )
    {
        parent::__construct( $error );
    }

    public function getNextPage()
    {
        if ( isset( $this->backButton     )) return new DatabaseConnectionPage();
        if ( isset( $this->continueButton )) return new StorySettingsPage();
        throw new HardStoryException( "Unrecognized navigation from admin account page." );
    }

    public function validate()
    {
        $result = DatabaseConnectionPage::validatePage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getSubtitle()
    {
        return "Administrator Account";
    }

    protected function renderMain()
    {
        $adminLoginName   = Util::getStringParamDefault( $_POST, "adminLoginName",   "" );
        $adminDisplayName = Util::getStringParamDefault( $_POST, "adminDisplayName", "" );
        $adminPassword1   = Util::getStringParamDefault( $_POST, "adminPassword1",   "" );
        $adminPassword2   = Util::getStringParamDefault( $_POST, "adminPassword2",   "" );

        $adminLoginNameField = new InputField(
                "adminLoginName", "Login Name", "text", $adminLoginName,
                "This is the login name for the administrative account for your story. This " .
                "account will be created during installation. You will be able to log in to this " .
                "account using this name." );

        $adminDisplayNameField = new InputField(
                "adminDisplayName", "Display Name", "text", $adminDisplayName,
                "This is the display name for the administrative account for your story. This " .
                "name will be publicly displayed on any moderation activity you perform in your " .
                "story." );

        $adminPassword1Field = new InputField(
                "adminPassword1", "Pasword", "password", $adminPassword1,
                "This is the password for the administrative account for your story. This " .
                "account will be created during installation. You will be able to log in to this " .
                "account using this password." );

        $adminPassword2Field = new InputField(
                "adminPassword2", "Pasword (Again)", "password", $adminPassword2,
                "Please enter the password a second time to guard against a mis-typed password." );

        $adminLoginNameField->render();
        $adminDisplayNameField->render();
        $adminPassword1Field->render();
        $adminPassword2Field->render();

?>

<div class="submit">
    <input type="hidden" name="pageName" value="AdminAccount" />
    <input type="submit" name="backButton" value="Back" />
    <input type="submit" name="continueButton" value="Continue" />
</div>

<?php

    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton",
                      "adminLoginName", "adminDisplayName", "adminPassword1", "adminPassword2" );
    }
}

?>
