<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2025 Jeffrey J. Weston <jjweston@gmail.com>


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

use \Extend_A_Story\Data\Database;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Upgrade\Version;
use \Extend_A_Story\Util;

class CompletedPage extends InstallPage
{
    public static function validate()
    {
        $result = ConfirmationPage::validatePage();
        if ( isset( $result )) return $result;
        return new CompletedPage();
    }

    private $databaseExists;
    private $databaseHost;
    private $databaseUsername;
    private $databasePassword;
    private $databaseName;
    private $isWriteable;

    protected function getPageTitle() { return ( $this->databaseExists ? "Upgrade" : "Install" ) . " Completed"; }

    protected function preRender()
    {
        $this->databaseExists   = Util::getBoolParam  ( $_POST, "databaseExists"   );
        $this->databaseHost     = Util::getStringParam( $_POST, "databaseHost"     );
        $this->databaseUsername = Util::getStringParam( $_POST, "databaseUsername" );
        $this->databasePassword = Util::getStringParam( $_POST, "databasePassword" );
        $this->databaseName     = Util::getStringParam( $_POST, "databaseName"     );

        if ( $this->databaseExists ) $this->upgradeDatabase();
        else $this->installDatabase();

        $this->isWriteable = Util::getStringValue( "IsWriteable" );
    }

    protected function renderMain()
    {

?>

<p>Your Extend-A-Story database has been <?php echo( $this->databaseExists ? "upgraded" : "installed" ); ?>.</p>

<h3>Update Configuration</h3>

<p>
    To finish your <?php echo( $this->databaseExists ? "upgrade" : "installation" ); ?>
    you must update your configuration file.
    This is the location of your configuration file:
</p>

<pre>
<?php echo( htmlentities( realpath( __DIR__ . "/../../../../config/Configuration.php" ))); ?>
</pre>

<p>Near the end of the file you will find a section that begins with:</p>

<pre>
$configInstallToken = "<?php echo( htmlentities( $this->installToken )); ?>";
</pre>

<p>Change that section to read as follows:</p>

<pre>
$configInstallToken     = null;
$configDatabaseHost     = "<?php echo( htmlentities( $this->databaseHost     )); ?>";
$configDatabaseUsername = "<?php echo( htmlentities( $this->databaseUsername )); ?>";
$configDatabasePassword = "<?php echo( htmlentities( $this->databasePassword )); ?>";
$configDatabaseName     = "<?php echo( htmlentities( $this->databaseName     )); ?>";
$configStoryEnabled     = true;
</pre>

<?php

        if ( !$this->databaseExists )
        {

?>

<h3>Create First Episode</h3>

<p>
    You must enable episode creation before you can create the first episode of your story.
    Navigate to your Extend-A-Story admninistration page:
    <a href="admin.php" target="_blank">Extend-A-Story Admninistration</a>
</p>

<p>
    Log in using the administrator account we created during installation,
    click <em>Configure Story Settings</em>,
    set <em>Is Writeable</em> to <em>Yes</em>,
    and click <em>Save</em>.
</p>

<p>
    Now you can create the first episode of your story.
    Navigate to your Extend-A-Story page for reading episodes:
    <a href="read.php" target="_blank">Read Episode</a>
</p>

<p>Follow the prompts to create the first episode and any subsequent episodes you wish to create.</p>

<?php

        }
        else if ( $this->isWriteable === "N" )
        {

?>

<h3>Enable Episode Creation</h3>

<p>
    Episode creation is currently disabled and must be enabled before new episodes can be created.
    Navigate to your Extend-A-Story admninistration page:
    <a href="admin.php" target="_blank">Extend-A-Story Admninistration</a>
</p>

<p>
    Log in using your administrator account,
    click <em>Configure Story Settings</em>,
    set <em>Is Writeable</em> to <em>Yes</em>,
    and click <em>Save</em>.
</p>

<?php

        }
    }

    private function installDatabase()
    {
        $settingsStoryName      = Util::getStringParam( $_POST, "settingsStoryName"      );
        $settingsSiteName       = Util::getStringParam( $_POST, "settingsSiteName"       );
        $settingsStoryHome      = Util::getStringParam( $_POST, "settingsStoryHome"      );
        $settingsSiteHome       = Util::getStringParam( $_POST, "settingsSiteHome"       );
        $settingsReadEpisodeUrl = Util::getStringParam( $_POST, "settingsReadEpisodeUrl" );
        $settingsAdminEmail     = Util::getStringParam( $_POST, "settingsAdminEmail"     );
        $settingsMaxLinks       = Util::getStringParam( $_POST, "settingsMaxLinks"       );
        $settingsMaxEditDays    = Util::getStringParam( $_POST, "settingsMaxEditDays"    );
        $adminLoginName         = Util::getStringParam( $_POST, "adminLoginName"         );
        $adminDisplayName       = Util::getStringParam( $_POST, "adminDisplayName"       );
        $adminPassword          = Util::getStringParam( $_POST, "adminPassword1"         );

        Database::createDatabase();
        Database::populateDatabase( $settingsStoryName, $settingsSiteName,
                                    $settingsStoryHome, $settingsSiteHome,
                                    $settingsReadEpisodeUrl, $settingsAdminEmail,
                                    $settingsMaxLinks, $settingsMaxEditDays,
                                    $adminLoginName, $adminDisplayName, $adminPassword );
    }

    private function upgradeDatabase()
    {
        $upgradeData = [];
        $version = Version::getVersion();

        if ( $version->getDatabaseVersion() === 1 )
        {
            $upgradeData[ "settingsMaxEditDays" ] = Util::getStringParam( $_POST, "settingsMaxEditDays" );
            $upgradeData[ "adminLoginName"      ] = Util::getStringParam( $_POST, "adminLoginName"      );
            $upgradeData[ "adminDisplayName"    ] = Util::getStringParam( $_POST, "adminDisplayName"    );
            $upgradeData[ "adminPassword"       ] = Util::getStringParam( $_POST, "adminPassword1"      );
        }

        $version->upgradeDatabase( $upgradeData );
    }
}

?>
