<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2024 Jeffrey J. Weston <jjweston@gmail.com>


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

namespace Extend_A_Story\HtmlElements;

class RadioButton extends HtmlElement
{
    private $name;
    private $id;
    private $value;
    private $checked;
    private $label;

    public function __construct( $name, $id, $value, $checked, $label )
    {
        $this->name    = $name;
        $this->id      = $id;
        $this->value   = $value;
        $this->checked = $checked;
        $this->label   = $label;
    }

    public function render()
    {

?>

<div>
    <input type="radio"
           id="<?php echo( htmlentities( $this->id )); ?>"
           name="<?php echo( htmlentities( $this->name )); ?>"
           value="<?php echo( htmlentities( $this->value )); ?>"
           <?php echo( $this->checked ? "checked" : "" ); ?>>
    <label for="<?php echo( htmlentities( $this->id )); ?>"> <?php echo( htmlentities( $this->label )); ?></label>
</div>

<?php

    }
}

?>
