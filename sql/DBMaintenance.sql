# Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
# Copyright (C) 2002-2025 Jeffrey J. Weston <jjweston@gmail.com>
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


### Find all Links that are incorrectly listed as Created or Not-Created.

SELECT Episode.EpisodeID, Episode.Status, Link.LinkID, Link.IsCreated, Link.SourceEpisodeID, Link.TargetEpisodeID
  FROM Episode, Link
 WHERE ( Episode.EpisodeID = Link.TargetEpisodeID ) AND
       (((( Episode.Status = 2 ) OR
          ( Episode.Status = 3 )) AND
         ( Link.IsCreated = "N" )) OR
        ((( Episode.Status = 0 ) OR
          ( Episode.Status = 1 )) AND
         ( Link.IsCreated = "Y" )))
 ORDER BY Episode.EpisodeID;


### Find all Links that are incorrectly listed as BackLinked or Not-BackLinked.

SELECT Episode.EpisodeID, Episode.Status, Link.LinkID, Link.IsBackLink, Link.SourceEpisodeID, Link.TargetEpisodeID
  FROM Episode, Link
 WHERE ( Episode.EpisodeID = Link.TargetEpisodeID ) AND
       ((( Episode.Parent = Link.SourceEpisodeID ) AND
         ( Link.IsBackLink = "Y" )) OR
        (( Episode.Parent != Link.SourceEpisodeID ) AND
         ( Link.IsBackLink = "N" )))
 ORDER BY Episode.EpisodeID;
