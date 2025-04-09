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

use \Extend_A_Story\HtmlElements\RawText;
use \Extend_A_Story\HtmlElements\UnorderedList;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Upgrade\Version;
use \Extend_A_Story\Util;

class VersionConfirmationPage extends InstallPage
{
    public static function validate()
    {
        $result = DatabaseConnectionPage::validatePage();
        if ( isset( $result )) return $result;
        return new VersionConfirmationPage();
    }

    public static function validatePage()
    {
        $result = DatabaseConnectionPage::validatePage();
        if ( isset( $result )) return $result;

        $version = Version::getVersion();

        // return to the version confirmation page if the database version or its creation status changed
        if (( Util::getIntParam ( $_POST, "databaseVersion" ) !== $version->getDatabaseVersion() ) or
            ( Util::getBoolParam( $_POST, "databaseExists"  ) !== $version->checkDatabase()      ))
        {
            $message = "The status of your database has changed. " .
                       "Verify that everything is as you expect before proceeding.";
            $error = new UnorderedList( [ new RawText( $message ) ] );
            return new VersionConfirmationPage( $error );
        }

        return null;
    }

    private $databaseVersion;
    private $databaseExists;
    private $storyVersion;
    private $nextButton;

    public function __construct( $error = null )
    {
        parent::__construct( $error );
    }

    protected function getPageTitle() { return "Version Confirmation"; }
    protected function getPageFields() { return [ "databaseVersion", "databaseExists" ]; }

    protected function preRender()
    {
        $version = Version::getVersion();
        $this->databaseVersion = $version->getDatabaseVersion();
        $this->databaseExists  = $version->checkDatabase();
        $this->storyVersion    = $version->getStoryVersion();

        // determine the next page
        if (( !$this->databaseExists ) || ( $this->databaseVersion === 1 )) $this->nextButton = "adminAccountButton";
        if (( $this->databaseVersion >= 2 ) && ( $this->databaseVersion <= 4 )) $this->nextButton = "confirmationButton";
    }

    protected function renderMain()
    {

?>

<p>

<?php

        if ( !$this->databaseExists )
        {

?>

We did not find an Extend-A-Story database.
We will proceed with installing a new Extend-A-Story database.

<?php

        }
        else
        {
            if ( $this->databaseVersion === 5 )
            {

?>

We found an Extend-A-Story database that is already the current version.
There is nothing for us to do.

<?php

            }
            else
            {

?>

We found an Extend-A-Story database for version <?php echo( htmlentities( $this->storyVersion )); ?> of Extend-A-Story.
We will proceed with upgrading your database to the current version.

<?php

            }
        }

?>

</p>

<p>If this is not what you expected, go back and check your database connection settings.</p>

<div class="submit">
    <input type="hidden" name="databaseVersion"          value="<?php echo( htmlentities( $this->databaseVersion    )); ?>">
    <input type="hidden" name="databaseExists"           value="<?php echo( $this->databaseExists ? "true" : "false" ); ?>">
    <input type="submit" name="databaseConnectionButton" value="Back"                                                      >

<?php

        if ( isset( $this->nextButton ))
        {

?>

    <input type="submit" name="<?php echo( htmlentities( $this->nextButton )); ?>" value="Continue">

<?php

        }

?>

</div>

<?php

    }
}

?>
