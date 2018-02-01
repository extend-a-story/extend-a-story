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
        $result = AuthorizationPage::validatePage();
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
            global $dbHost, $dbUser, $dbPassword, $dbDatabase;

            $dbHost     = $databaseHost;
            $dbUser     = $databaseUsername;
            $dbPassword = $databasePassword;
            $dbDatabase = $databaseName;

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

    public function __construct( $error = null )
    {
        parent::__construct( $error );
    }

    public function getNextPage()
    {
        $allowDataLoss = Util::getStringParamDefault( $_POST, "allowDataLoss", null );
        if ( isset( $this->backButton )) return new StartPage();
        if (( isset( $this->continueButton )) and ( isset( $allowDataLoss ))) return new DataLossWarningPage();
        if (( isset( $this->continueButton )) and ( !isset( $allowDataLoss ))) return new AdminAccountPage();
        throw new StoryException( "Unrecognized navigation from database connection page." );
    }

    public function validate()
    {
        $result = AuthorizationPage::validatePage();
        if ( isset( $result )) return $result;
        return $this;
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

    protected function renderMain()
    {
        $databaseHost     = Util::getStringParamDefault( $_POST, "databaseHost",     "" );
        $databaseUsername = Util::getStringParamDefault( $_POST, "databaseUsername", "" );
        $databasePassword = Util::getStringParamDefault( $_POST, "databasePassword", "" );
        $databaseName     = Util::getStringParamDefault( $_POST, "databaseName",     "" );

        $databaseHostField = new InputField(
                "databaseHost", "Host", "text", $databaseHost,
                "This is the host name for your database server. If your database server and " .
                "your web server are running on the same machine, use \"localhost\". If you are " .
                "running Extend-A-Story in a shared hosting environment, your hosting provider " .
                "will provide you with the host name for your database server." );

        $databaseUsernameField = new InputField(
                "databaseUsername", "Username", "text", $databaseUsername,
                "This is the username that will be used to connect to your database server " .
                "during the installation process. This user will need all permissions to your " .
                "Extend-A-Story database." );

        $databasePasswordField = new InputField(
                "databasePassword", "Password", "password", $databasePassword,
                "This is the password that will be used to connect to your database server " .
                "during the installation process." );

        $databaseNameField = new InputField(
                "databaseName", "Database", "text", $databaseName,
                "This is the name of your Extend-A-Story database. The tables needed by " .
                "Extend-A-Story will be created in this database." );

        $databaseHostField->render();
        $databaseUsernameField->render();
        $databasePasswordField->render();
        $databaseNameField->render();

?>

<div class="submit">
    <input type="hidden" name="pageName" value="DatabaseConnection" />
    <input type="submit" name="backButton" value="Back" />
    <input type="submit" name="continueButton" value="Continue" />
</div>

<?php

    }
}

?>
