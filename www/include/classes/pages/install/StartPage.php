<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2014 Jeffrey J. Weston and Matthew Duhan


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

namespace pages\install;

use \Extend_A_Story\Pages\Install\InstallPage;

class StartPage extends InstallPage
{
    public function renderMain()
    {

?>

<h2>Welcome to Extend-A-Story</h2>

<p>
    This page will guide you through the Extend-A-Story installation. Click the Continue button to
    proceed.
</p>
<div class="submit">
    <form action="install.php" method="post">
        <input type="hidden" name="step" value="Settings" />
        <input type="submit" value="Continue" />
    </form>
</div>

<?php

    }
}

?>
