# Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
# Copyright (C) 2002 - 2003  Extend-A-Story Development Team
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

select Episode.EpisodeID, Episode.Status, Link.LinkID, Link.IsCreated, Link.SourceEpisodeID, Link.TargetEpisodeID
from Episode, Link
where Episode.EpisodeID = Link.TargetEpisodeID and
( ( ( Episode.Status = 2 or Episode.Status = 3 ) and Link.IsCreated = "N" ) or
  ( ( Episode.Status = 0 or Episode.Status = 1 ) and Link.IsCreated = "Y" ) )
order by Episode.EpisodeID;


### Find all Links that are incorrectly listed as BackLinked or Not-BackLinked.

select Episode.EpisodeID, Episode.Status, Link.LinkID, Link.IsBackLink, Link.SourceEpisodeID, Link.TargetEpisodeID
from Episode, Link
where Episode.EpisodeID = Link.TargetEpisodeID and
( ( Episode.Parent  = Link.SourceEpisodeID and Link.IsBackLink = "Y" ) or
  ( Episode.Parent != Link.SourceEpisodeID and Link.IsBackLink = "N" ) )
order by Episode.EpisodeID;
