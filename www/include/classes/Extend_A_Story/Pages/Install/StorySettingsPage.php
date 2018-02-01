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

use \Extend_A_Story\HtmlElements\InputField;
use \Extend_A_Story\HtmlElements\RawText;
use \Extend_A_Story\HtmlElements\UnorderedList;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class StorySettingsPage extends InstallPage
{
    public static function validatePage()
    {
        $result = AdminAccountPage::validatePage();
        if ( isset( $result )) return $result;

        $settingsStoryName      = Util::getStringParamDefault( $_POST, "settingsStoryName",      "" );
        $settingsSiteName       = Util::getStringParamDefault( $_POST, "settingsSiteName",       "" );
        $settingsStoryHome      = Util::getStringParamDefault( $_POST, "settingsStoryHome",      "" );
        $settingsSiteHome       = Util::getStringParamDefault( $_POST, "settingsSiteHome",       "" );
        $settingsReadEpisodeUrl = Util::getStringParamDefault( $_POST, "settingsReadEpisodeUrl", "" );
        $settingsAdminEmail     = Util::getStringParamDefault( $_POST, "settingsAdminEmail",     "" );
        $settingsMaxLinks       = Util::getStringParamDefault( $_POST, "settingsMaxLinks",       "" );
        $settingsMaxEditDays    = Util::getStringParamDefault( $_POST, "settingsMaxEditDays",    "" );

        $errors = array();

        if ( strlen( $settingsStoryName ) == 0 )
        {
            $errors[] = new RawText( "Story name must be set." );
        }

        if ( strlen( $settingsSiteName ) == 0 )
        {
            $errors[] = new RawText( "Site name must be set." );
        }

        if ( strlen( $settingsStoryHome ) == 0 )
        {
            $errors[] = new RawText( "Story home must be set." );
        }

        if ( strlen( $settingsSiteHome ) == 0 )
        {
            $errors[] = new RawText( "Site home must be set." );
        }

        if ( strlen( $settingsReadEpisodeUrl ) == 0 )
        {
            $errors[] = new RawText( "Read episode URL must be set." );
        }

        if ( strlen( $settingsAdminEmail ) == 0 )
        {
            $errors[] = new RawText( "Admin email must be set." );
        }

        if ( strlen( $settingsMaxLinks ) == 0 )
        {
            $errors[] = new RawText( "Max links must be set." );
        }
        else if ( !ctype_digit( $settingsMaxLinks ))
        {
            $errors[] = new RawText( "Max links must be a positive integer." );
        }
        else if (( (int) $settingsMaxLinks ) <= 0 )
        {
            $errors[] = new RawText( "Max links must be greater than zero." );
        }

        if ( strlen( $settingsMaxEditDays ) == 0 )
        {
            $errors[] = new RawText( "Max edit days must be set." );
        }
        else if ( !ctype_digit( $settingsMaxEditDays ))
        {
            $errors[] = new RawText( "Max edit days must be a positive integer." );
        }
        else if (( (int) $settingsMaxEditDays ) <= 0 )
        {
            $errors[] = new RawText( "Max edit days must be greater than zero." );
        }

        if ( count( $errors ) > 0 )
        {
            return new StorySettingsPage( new UnorderedList( $errors ));
        }

        return null;
    }

    public function __construct( $error = null )
    {
        parent::__construct( $error );
    }

    public function getNextPage()
    {
        if ( isset( $this->backButton     )) return new AdminAccountPage();
        if ( isset( $this->continueButton )) return new ConfirmationPage();
        throw new StoryException( "Unrecognized navigation from story settings page." );
    }

    public function validate()
    {
        $result = AdminAccountPage::validatePage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getSubtitle()
    {
        return "Story Settings";
    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton",
                      "settingsStoryName", "settingsSiteName",
                      "settingsStoryHome", "settingsSiteHome",
                      "settingsReadEpisodeUrl", "settingsAdminEmail",
                      "settingsMaxLinks", "settingsMaxEditDays" );
    }

    protected function renderMain()
    {
        $settingsStoryName      = Util::getStringParamDefault( $_POST, "settingsStoryName",      "" );
        $settingsSiteName       = Util::getStringParamDefault( $_POST, "settingsSiteName",       "" );
        $settingsStoryHome      = Util::getStringParamDefault( $_POST, "settingsStoryHome",      "" );
        $settingsSiteHome       = Util::getStringParamDefault( $_POST, "settingsSiteHome",       "" );
        $settingsReadEpisodeUrl = Util::getStringParamDefault( $_POST, "settingsReadEpisodeUrl", "" );
        $settingsAdminEmail     = Util::getStringParamDefault( $_POST, "settingsAdminEmail",     "" );
        $settingsMaxLinks       = Util::getStringParamDefault( $_POST, "settingsMaxLinks",       "" );
        $settingsMaxEditDays    = Util::getStringParamDefault( $_POST, "settingsMaxEditDays",    "" );

        $settingsStoryNameField = new InputField(
                "settingsStoryName", "Story Name", "text", $settingsStoryName,
                "This is the name of your story. This name will be used in page titles and links " .
                "to the home page of your story." );

        $settingsSiteNameField = new InputField(
                "settingsSiteName", "Site Name", "text", $settingsSiteName,
                "This is the name of your web site. This name will be used in links to the home " .
                "page of your web site." );

        $settingsStoryHomeField = new InputField(
                "settingsStoryHome", "Story Home", "text", $settingsStoryHome,
                "This is the URL for the home page of your story. All story pages will provide a " .
                "link to this URL." );

        $settingsSiteHomeField = new InputField(
                "settingsSiteHome", "Site Home", "text", $settingsSiteHome,
                "This is the URL for the home page of your web site. All story pages will " .
                "provide a link to this URL." );

        $settingsReadEpisodeUrlField = new InputField(
                "settingsReadEpisodeUrl", "Read Episode URL", "text", $settingsReadEpisodeUrl,
                "This is the URL to the \"read.php\" script for this story on your web site. " .
                "Email notifications of newly created episodes will use this URL to provide a " .
                "link to the newly created episode." );

        $settingsAdminEmailField = new InputField(
                "settingsAdminEmail", "Admin Email", "text", $settingsAdminEmail,
                "This is the email address from which email notifications of newly created " .
                "episodes will be sent. This email address will receive an email notification " .
                "for every episode that is created." );

        $settingsMaxLinksField = new InputField(
                "settingsMaxLinks", "Max Links", "text", $settingsMaxLinks,
                "This is the maximum number of links an author is allowed to specify when " .
                "creating an episode." );

        $settingsMaxEditDaysField = new InputField(
                "settingsMaxEditDays", "Max Edit Days", "text", $settingsMaxEditDays,
                "This is the number of days for which an author is allowed to edit an epiosde " .
                "that they created." );

        $settingsStoryNameField->render();
        $settingsSiteNameField->render();
        $settingsStoryHomeField->render();
        $settingsSiteHomeField->render();
        $settingsReadEpisodeUrlField->render();
        $settingsAdminEmailField->render();
        $settingsMaxLinksField->render();
        $settingsMaxEditDaysField->render();

?>

<div class="submit">
    <input type="hidden" name="pageName" value="StorySettings" />
    <input type="submit" name="backButton" value="Back" />
    <input type="submit" name="continueButton" value="Continue" />
</div>

<?php

    }
}

?>
