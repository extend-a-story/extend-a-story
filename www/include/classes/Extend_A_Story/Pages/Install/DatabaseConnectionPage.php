<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2022 Jeffrey J. Weston <jjweston@gmail.com>


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

use \Exception;

use \Extend_A_Story\HtmlElements\InputField;
use \Extend_A_Story\HtmlElements\RawText;
use \Extend_A_Story\HtmlElements\UnorderedList;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class DatabaseConnectionPage extends InstallPage
{
    public static function validatePage()
    {
        $result = DatabaseConnectionPage::validatePreviousPage();
        if ( isset( $result )) return $result;

        $databaseHost     = Util::getStringParamDefault( $_POST, "databaseHost",     "" );
        $databaseUsername = Util::getStringParamDefault( $_POST, "databaseUsername", "" );
        $databasePassword = Util::getStringParamDefault( $_POST, "databasePassword", "" );
        $databaseName     = Util::getStringParamDefault( $_POST, "databaseName",     "" );

        $errors = array();

        if ( strlen( $databaseHost ) == 0 )
        {
            $errors[] = new RawText( "Host must be set." );
        }

        if ( strlen( $databaseUsername ) == 0 )
        {
            $errors[] = new RawText( "Username must be set." );
        }

        if ( strlen( $databasePassword ) == 0 )
        {
            $errors[] = new RawText( "Password must be set." );
        }

        if ( strlen( $databaseName ) == 0 )
        {
            $errors[] = new RawText( "Database must be set." );
        }

        if ( count( $errors ) == 0 )
        {
            global $configDatabaseHost, $configDatabaseUsername, $configDatabasePassword, $configDatabaseName,
                   $configStoryEnabled;

            $configDatabaseHost     = $databaseHost;
            $configDatabaseUsername = $databaseUsername;
            $configDatabasePassword = $databasePassword;
            $configDatabaseName     = $databaseName;
            $configStoryEnabled     = true;

            try
            {
                Util::getDbConnection();
            }
            catch ( Exception $e )
            {
                $errors[] = new RawText( "Unable to connect to database: " . $e->getMessage() );
            }
        }

        if ( count( $errors ) > 0 )
        {
            return new DatabaseConnectionPage( new UnorderedList( $errors ));
        }

        return null;
    }

    private static function validatePreviousPage()
    {
        $result = StartPage::validatePage();
        if ( isset( $result )) return $result;
        return null;
    }

    private $databaseHostField;
    private $databaseUsernameField;
    private $databasePasswordField;
    private $databaseNameField;

    public function __construct( $error = null )
    {
        parent::__construct( $error );
    }

    public function validate()
    {
        $result = DatabaseConnectionPage::validatePreviousPage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getNextPage()
    {
        if ( isset( $this->backButton )) return new StartPage();
        if ( isset( $this->continueButton )) return new SelectTaskPage();
        throw new StoryException( "Unrecognized navigation from database connection page." );
    }

    protected function getSubtitle()
    {
        return "Database Connection";
    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton",
                      "databaseHost", "databaseUsername", "databasePassword", "databaseName" );
    }

    protected function preRender()
    {
        global $configDatabaseHost, $configDatabaseUsername, $configDatabasePassword, $configDatabaseName;

        $databaseHost     = Util::getStringParamDefault( $_POST, "databaseHost",     null );
        $databaseUsername = Util::getStringParamDefault( $_POST, "databaseUsername", null );
        $databasePassword = Util::getStringParamDefault( $_POST, "databasePassword", null );
        $databaseName     = Util::getStringParamDefault( $_POST, "databaseName",     null );

        if ( !isset( $databaseHost     )) $databaseHost     = $configDatabaseHost;
        if ( !isset( $databaseUsername )) $databaseUsername = $configDatabaseUsername;
        if ( !isset( $databasePassword )) $databasePassword = $configDatabasePassword;
        if ( !isset( $databaseName     )) $databaseName     = $configDatabaseName;

        if ( !isset( $databaseHost     )) $databaseHost     = "";
        if ( !isset( $databaseUsername )) $databaseUsername = "";
        if ( !isset( $databasePassword )) $databasePassword = "";
        if ( !isset( $databaseName     )) $databaseName     = "";

        $this->databaseHostField = new InputField(
                "databaseHost", "Host", "text", $databaseHost,
                "This is the hostname for your database server. If your database server and your web server are " .
                "running on the same machine, use \"localhost\". If you are running Extend-A-Story in a shared " .
                "hosting environment, your hosting provider will provide you with the hostname for your database " .
                "server." );

        $this->databaseUsernameField = new InputField(
                "databaseUsername", "Username", "text", $databaseUsername,
                "This is the username for connecting to your database server. This user needs full permissions to " .
                "your Extend-A-Story database." );

        $this->databasePasswordField = new InputField(
                "databasePassword", "Password", "password", $databasePassword,
                "This is the password for connecting to your database server." );

        $this->databaseNameField = new InputField(
                "databaseName", "Database", "text", $databaseName,
                "This is the name of your Extend-A-Story database." );
    }

    protected function renderMain()
    {

?>

<p>
    Tell us how to connect to your database.
</p>

<?php

        $this->databaseHostField->render();
        $this->databaseUsernameField->render();
        $this->databasePasswordField->render();
        $this->databaseNameField->render();

?>

<div class="submit">
    <input type="hidden" name="pageName" value="DatabaseConnection">
    <input type="submit" name="backButton" value="Back">
    <input type="submit" name="continueButton" value="Continue">
</div>

<?php

    }
}

?>
