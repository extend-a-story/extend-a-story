# Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
# Copyright (C) 2002-2017 Jeffrey J. Weston
#
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
#
# For information about Extend-A-Story and its authors, please visit the website:
# http://www.sir-toby.com/extend-a-story/


ALTER TABLE User MODIFY COLUMN Password  VARCHAR( 255 )  NOT NULL;

ALTER TABLE Session MODIFY COLUMN SessionID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT;
DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextSessionID';

ALTER TABLE User MODIFY COLUMN UserID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT;
DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextUserID';

ALTER TABLE Episode MODIFY COLUMN EpisodeID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT;
DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextEpisodeID';

ALTER TABLE Link MODIFY COLUMN LinkID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT;
DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextLinkID';

ALTER TABLE EpisodeEditLog MODIFY COLUMN EpisodeEditLogID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT;
DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextEpisodeEditLogID';

ALTER TABLE LinkEditLog MODIFY COLUMN LinkEditLogID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT;
DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextLinkEditLogID';

ALTER TABLE Scheme MODIFY COLUMN SchemeID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT;
DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextSchemeID';

ALTER TABLE Image MODIFY COLUMN ImageID  INT UNSIGNED  NOT NULL  AUTO_INCREMENT;
DELETE FROM ExtendAStoryVariable WHERE VariableName = 'NextImageID';
