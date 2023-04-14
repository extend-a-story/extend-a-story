<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2023 Jeffrey J. Weston <jjweston@gmail.com>


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

use \Extend_A_Story\Data\ExtendAStoryVariable;
use \Extend_A_Story\HtmlElements\InputField;
use \Extend_A_Story\HtmlElements\RawText;
use \Extend_A_Story\HtmlElements\UnorderedList;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class StorySettingsPage extends InstallPage
{
    public static function validate()
    {
        $result = AdminAccountPage::validatePage();
        if ( isset( $result )) return $result;
        return new StorySettingsPage();
    }

    public static function validatePage()
    {
        $result = AdminAccountPage::validatePage();
        if ( isset( $result )) return $result;

        // skip validation if the database exists and is a version that already has story settings
        $databaseExists  = Util::getBoolParam( $_POST, "databaseExists"  );
        $databaseVersion = Util::getIntParam ( $_POST, "databaseVersion" );
        if (( $databaseExists ) && ( $databaseVersion > 1 )) return null;

        $settingsStoryName      = Util::getStringParamDefault( $_POST, "settingsStoryName",      "" );
        $settingsSiteName       = Util::getStringParamDefault( $_POST, "settingsSiteName",       "" );
        $settingsStoryHome      = Util::getStringParamDefault( $_POST, "settingsStoryHome",      "" );
        $settingsSiteHome       = Util::getStringParamDefault( $_POST, "settingsSiteHome",       "" );
        $settingsReadEpisodeUrl = Util::getStringParamDefault( $_POST, "settingsReadEpisodeUrl", "" );
        $settingsAdminEmail     = Util::getStringParamDefault( $_POST, "settingsAdminEmail",     "" );
        $settingsMaxLinks       = Util::getStringParamDefault( $_POST, "settingsMaxLinks",       "" );
        $settingsMaxEditDays    = Util::getStringParamDefault( $_POST, "settingsMaxEditDays",    "" );

        $errors = [];

        if ( !$databaseExists )
        {
            $max = ExtendAStoryVariable::stringValueLimit;

            if ( strlen( $settingsStoryName ) == 0   ) $errors[] = new RawText( "Story name must be set." );
            if ( strlen( $settingsStoryName ) > $max ) $errors[] = new RawText( "Story name is too long." );

            if ( strlen( $settingsSiteName ) == 0   ) $errors[] = new RawText( "Site name must be set." );
            if ( strlen( $settingsSiteName ) > $max ) $errors[] = new RawText( "Site name is too long." );

            if ( strlen( $settingsStoryHome ) == 0   ) $errors[] = new RawText( "Story home must be set." );
            if ( strlen( $settingsStoryHome ) > $max ) $errors[] = new RawText( "Story home is too long." );

            if ( strlen( $settingsSiteHome ) ==  0  ) $errors[] = new RawText( "Site home must be set." );
            if ( strlen( $settingsSiteHome ) > $max ) $errors[] = new RawText( "Site home is too long." );

            if ( strlen( $settingsReadEpisodeUrl ) == 0   ) $errors[] = new RawText( "Read episode URL must be set." );
            if ( strlen( $settingsReadEpisodeUrl ) > $max ) $errors[] = new RawText( "Read episode URL is too long." );

            if ( strlen( $settingsAdminEmail ) == 0   ) $errors[] = new RawText( "Admin email must be set." );
            if ( strlen( $settingsAdminEmail ) > $max ) $errors[] = new RawText( "Admin email is too long." );

            if ( strlen( $settingsMaxLinks ) == 0 )      $errors[] = new RawText( "Max links must be set."                );
            else if ( !ctype_digit( $settingsMaxLinks )) $errors[] = new RawText( "Max links must be a positive integer." );
            else if (( (int) $settingsMaxLinks ) <= 0 )  $errors[] = new RawText( "Max links must be greater than zero."  );
        }

        if ( strlen( $settingsMaxEditDays ) == 0 )      $errors[] = new RawText( "Max edit days must be set."                );
        else if ( !ctype_digit( $settingsMaxEditDays )) $errors[] = new RawText( "Max edit days must be a positive integer." );
        else if (( (int) $settingsMaxEditDays ) <= 0 )  $errors[] = new RawText( "Max edit days must be greater than zero."  );

        if ( count( $errors ) > 0 ) return new StorySettingsPage( new UnorderedList( $errors ));
        return null;
    }

    private $databaseExists;
    private $settingsStoryNameField;
    private $settingsSiteNameField;
    private $settingsStoryHomeField;
    private $settingsSiteHomeField;
    private $settingsReadEpisodeUrlField;
    private $settingsAdminEmailField;
    private $settingsMaxLinksField;
    private $settingsMaxEditDaysField;

    public function __construct( $error = null )
    {
        parent::__construct( $error );
    }

    protected function getPageTitle() { return "Story Settings"; }

    protected function getPageFields()
    {
        return [ "settingsStoryName", "settingsSiteName", "settingsStoryHome", "settingsSiteHome",
                 "settingsReadEpisodeUrl", "settingsAdminEmail", "settingsMaxLinks", "settingsMaxEditDays" ];
    }

    protected function preRender()
    {
        $this->databaseExists = Util::getBoolParam( $_POST, "databaseExists" );

        $settingsStoryName      = Util::getStringParamDefault( $_POST, "settingsStoryName",      "" );
        $settingsSiteName       = Util::getStringParamDefault( $_POST, "settingsSiteName",       "" );
        $settingsStoryHome      = Util::getStringParamDefault( $_POST, "settingsStoryHome",      "" );
        $settingsSiteHome       = Util::getStringParamDefault( $_POST, "settingsSiteHome",       "" );
        $settingsReadEpisodeUrl = Util::getStringParamDefault( $_POST, "settingsReadEpisodeUrl", "" );
        $settingsAdminEmail     = Util::getStringParamDefault( $_POST, "settingsAdminEmail",     "" );
        $settingsMaxLinks       = Util::getStringParamDefault( $_POST, "settingsMaxLinks",       "" );
        $settingsMaxEditDays    = Util::getStringParamDefault( $_POST, "settingsMaxEditDays",    "" );

        $limit     = ExtendAStoryVariable::stringValueLimit;
        $threshold = ExtendAStoryVariable::stringValueThreshold;

        $this->settingsStoryNameField = new InputField(
                "settingsStoryName", "Story Name", "text", $settingsStoryName, null, $limit, $threshold,
                "This is the name of your story. This name will be used in page titles and links to the home page of " .
                "your story." );

        $this->settingsSiteNameField = new InputField(
                "settingsSiteName", "Site Name", "text", $settingsSiteName, null, $limit, $threshold,
                "This is the name of your web site. This name will be used in links to the home page of your web " .
                "site." );

        $this->settingsStoryHomeField = new InputField(
                "settingsStoryHome", "Story Home", "text", $settingsStoryHome, null, $limit, $threshold,
                "This is the URL for the home page of your story. All story pages will provide a link to this URL." );

        $this->settingsSiteHomeField = new InputField(
                "settingsSiteHome", "Site Home", "text", $settingsSiteHome, null, $limit, $threshold,
                "This is the URL for the home page of your web site. All story pages will provide a link to this " .
                "URL." );

        $this->settingsReadEpisodeUrlField = new InputField(
                "settingsReadEpisodeUrl", "Read Episode URL", "text", $settingsReadEpisodeUrl, null, $limit, $threshold,
                "This is the URL to the \"read.php\" script for this story on your web site. Email notifications of " .
                "newly created episodes will use this URL to provide a link to the newly created episode." );

        $this->settingsAdminEmailField = new InputField(
                "settingsAdminEmail", "Admin Email", "text", $settingsAdminEmail, null, $limit, $threshold,
                "This is the email address from which email notifications of newly created episodes will be sent. " .
                "This email address will also receive an email notification for every episode that is created." );

        $this->settingsMaxLinksField = new InputField(
                "settingsMaxLinks", "Max Links", "text", $settingsMaxLinks, Util::smallInputWidth, null, null,
                "This is the maximum number of links an author is allowed to specify when creating an episode." );

        $this->settingsMaxEditDaysField = new InputField(
                "settingsMaxEditDays", "Max Edit Days", "text", $settingsMaxEditDays, Util::smallInputWidth, null, null,
                "This is the number of days for which an author is allowed to edit an epiosde that they created." );
    }

    protected function renderMain()
    {

?>

<p>
    Configure your story settings.
</p>

<?php

        if ( !$this->databaseExists )
        {
            $this->settingsStoryNameField->render();
            $this->settingsSiteNameField->render();
            $this->settingsStoryHomeField->render();
            $this->settingsSiteHomeField->render();
            $this->settingsReadEpisodeUrlField->render();
            $this->settingsAdminEmailField->render();
            $this->settingsMaxLinksField->render();
        }

        $this->settingsMaxEditDaysField->render();

?>

<div class="submit">
    <input type="submit" name="adminAccountButton" value="Back"    >
    <input type="submit" name="confirmationButton" value="Continue">
</div>

<?php

    }
}

?>
