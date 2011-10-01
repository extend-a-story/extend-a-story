# Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
# Copyright (C) 2002 - 2011  Jeffrey J. Weston, Matthew Duhan
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


create table User
(
  UserID          int     unsigned not null primary key,
  PermissionLevel tinyint unsigned not null,
  LoginName       varchar( 255 )   not null,
  Password        char   ( 16  )   not null,
  UserName        varchar( 255 )   not null
);

create table EpisodeEditLog
(
  EpisodeEditLogID  int     unsigned not null primary key,
  EpisodeID         int     unsigned not null,
  SchemeID          int     unsigned not null,
  ImageID           int     unsigned not null,
  IsLinkable        char   ( 1   )   not null,
  IsExtendable      char   ( 1   )   not null,
  AuthorMailto      char   ( 1   )   not null,
  AuthorNotify      char   ( 1   )   not null,
  Title             varchar( 255 )   not null,
  Text              text             not null,
  AuthorName        varchar( 255 )   not null,
  AuthorEmail       varchar( 255 )   not null,
  EditDate          varchar( 255 )   not null,
  EditLogEntry      varchar( 255 )   not null,
  INDEX( EpisodeID )
);

create table LinkEditLog
(
  LinkEditLogID    int unsigned   not null primary key,
  EpisodeEditLogID int unsigned   not null,
  TargetEpisodeID  int unsigned   not null,
  IsBackLink       char   ( 1   ) not null,
  Description      varchar( 255 ) not null,
  INDEX( EpisodeEditLogID )
);

alter table Session add column UserID int unsigned not null after SessionID;

insert into ExtendAStoryVariable values( "NextUserID",           2,  null );
insert into ExtendAStoryVariable values( "NextEpisodeEditLogID", 1,  null );
insert into ExtendAStoryVariable values( "NextLinkEditLogID",    1,  null );
insert into ExtendAStoryVariable values( "MaxEditDays",          30, null );

insert into User values( 1, 4, "admin", password( "change-me" ), "Administrator" );
