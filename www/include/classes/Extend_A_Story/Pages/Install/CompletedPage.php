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

use \Extend_A_Story\Data\Tables;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class CompletedPage extends InstallPage
{
    public function validate()
    {
        $result = StorySettingsPage::validatePage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getNextPage()
    {
        throw new StoryException( "Unrecognized navigation from completed page." );
    }

    protected function getSubtitle()
    {
        return "Installation Completed";
    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton" );
    }

    protected function preRender()
    {
        $settingsStoryName      = Util::getStringParamDefault( $_POST, "settingsStoryName",      "" );
        $settingsSiteName       = Util::getStringParamDefault( $_POST, "settingsSiteName",       "" );
        $settingsStoryHome      = Util::getStringParamDefault( $_POST, "settingsStoryHome",      "" );
        $settingsSiteHome       = Util::getStringParamDefault( $_POST, "settingsSiteHome",       "" );
        $settingsReadEpisodeUrl = Util::getStringParamDefault( $_POST, "settingsReadEpisodeUrl", "" );
        $settingsAdminEmail     = Util::getStringParamDefault( $_POST, "settingsAdminEmail",     "" );
        $settingsMaxLinks       = Util::getStringParamDefault( $_POST, "settingsMaxLinks",       "" );
        $settingsMaxEditDays    = Util::getStringParamDefault( $_POST, "settingsMaxEditDays",    "" );
        $adminLoginName         = Util::getStringParamDefault( $_POST, "adminLoginName",         "" );
        $adminDisplayName       = Util::getStringParamDefault( $_POST, "adminDisplayName",       "" );
        $adminPassword          = Util::getStringParamDefault( $_POST, "adminPassword1",         "" );

        Tables::createTables();
        Tables::populateTables( $settingsStoryName, $settingsSiteName,
                                $settingsStoryHome, $settingsSiteHome,
                                $settingsReadEpisodeUrl, $settingsAdminEmail,
                                $settingsMaxLinks, $settingsMaxEditDays,
                                $adminLoginName, $adminDisplayName, $adminPassword );
    }

    protected function renderMain()
    {

?>

<p>This page is not complete.</p>

<?php

    }
}
