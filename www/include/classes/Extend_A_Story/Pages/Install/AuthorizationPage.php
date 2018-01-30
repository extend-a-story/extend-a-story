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

use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class AuthorizationPage extends InstallPage
{
    public static function validatePage()
    {
        global $installToken;
        $installTokenPost   = Util::getStringParamDefault( $_POST,   "installToken", null );
        $installTokenCookie = Util::getStringParamDefault( $_COOKIE, "installToken", null );
        $installTokenLocal  = isset( $installTokenPost ) ? $installTokenPost : $installTokenCookie;

        // allow installation to proceed if the install token is configured and matches
        if (( isset( $installToken )) && ( $installToken === $installTokenLocal )) return null;

        // otherwise, force the user to configure the install token
        return new AuthorizationPage();
    }

    public function getNextPage()
    {
        if ( isset( $this->continueButton )) return new StartPage();
        throw new StoryException( "Unrecognized navigation from authorization page." );
    }

    protected function getSubtitle()
    {
        return "Authorization Required";
    }

    protected function renderMain()
    {

?>

<p>
    You are attempting to install Extend-A-Story. You cannot proceed until you have verified that
    you are the owner of this site by updating your configuration file.
</p>
<p>
    This is the location of your configuration file:<br/>
    <code><?php echo( realpath( __DIR__ . "/../../../../config/Configuration.php" )); ?></code>
</p>
<p>
    Find the line that begins with <code>$installToken</code> and change it to read as follows:<br/>
    <code>$installToken = "<?php echo( $this->installToken ); ?>";</code>
</p>
<p>
    Once this is done, click the Continue button to proceed.
</p>

<div class="submit">
    <input type="hidden" name="pageName" value="Authorization" />
    <input type="submit" name="continueButton" value="Continue" />
</div>

<?php

    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton" );
    }
}

?>
