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


drop table if exists ExtendAStoryVariable;
drop table if exists Session;
drop table if exists User;
drop table if exists Episode;
drop table if exists Link;
drop table if exists EpisodeEditLog;
drop table if exists LinkEditLog;
drop table if exists Scheme;
drop table if exists Image;

create table ExtendAStoryVariable
(
  VariableName varchar( 255 ) not null primary key,
  IntValue     int unsigned,
  StringValue  varchar( 255 )
);

create table Session
(
  SessionID  int unsigned not null primary key,
  UserID     int unsigned not null,
  SessionKey int unsigned not null,
  AccessDate datetime     not null
);

create table User
(
  UserID          int     unsigned not null primary key,
  PermissionLevel tinyint unsigned not null,
  LoginName       varchar( 255 )   not null,
  Password        char   ( 16  )   not null,
  UserName        varchar( 255 )   not null
);

# Description of PermissionLevel values:
#   1 - Moderator       : Can edit any episode.
#   2 - Super Moderator : As above, but can also modify link structure.
#   3 - Manager         : As above, but can also change configuration values.
#   4 - Administrator   : As above, but can also edit users.

create table Episode
(
  EpisodeID         int     unsigned not null primary key,
  Parent            int     unsigned not null,
  AuthorSessionID   int     unsigned not null,
  EditorSessionID   int     unsigned not null,
  SchemeID          int     unsigned not null,
  ImageID           int     unsigned not null,
  Status            tinyint unsigned not null,
  IsLinkable        char   ( 1   )   not null,
  IsExtendable      char   ( 1   )   not null,
  AuthorMailto      char   ( 1   )   not null,
  AuthorNotify      char   ( 1   )   not null,
  Title             varchar( 255 )   not null,
  Text              text             not null,
  AuthorName        varchar( 255 )   not null,
  AuthorEmail       varchar( 255 )   not null,
  CreationDate      varchar( 255 )   not null,
  LockDate          varchar( 255 )   not null,
  LockKey           int     unsigned not null,
  CreationTimestamp datetime
);

# Description of Status values:
#   0 - Not Created
#   1 - Not Created / Locked for Creation
#   2 - Created
#   3 - Created / Locked for Editing

create table Link
(
  LinkID          int unsigned   not null primary key,
  SourceEpisodeID int unsigned   not null,
  TargetEpisodeID int unsigned   not null,
  IsCreated       char   ( 1   ) not null,
  IsBackLink      char   ( 1   ) not null,
  Description     varchar( 255 ) not null,
  INDEX( SourceEpisodeID ),
  INDEX( TargetEpisodeID )
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

create table Scheme
(
  SchemeID       int unsigned   not null primary key,
  SchemeName     varchar( 255 ) not null,
  bgcolor        varchar( 255 ) not null,
  text           varchar( 255 ) not null,
  link           varchar( 255 ) not null,
  vlink          varchar( 255 ) not null,
  alink          varchar( 255 ) not null,
  background     varchar( 255 ) not null,
  UncreatedLink  varchar( 255 ) not null,
  CreatedLink    varchar( 255 ) not null,
  BackLinkedLink varchar( 255 ) not null
);

create table Image
(
  ImageID   int unsigned   not null primary key,
  ImageName varchar( 255 ) not null,
  ImageURL  varchar( 255 ) not null
);

insert into ExtendAStoryVariable values( "CountDate",            null, date_format( now( ), '%c/%e/%Y %l:%i:%s %p' ) );
insert into ExtendAStoryVariable values( "CountValue",           0,    null );
insert into ExtendAStoryVariable values( "NextSessionID",        1,    null );
insert into ExtendAStoryVariable values( "NextUserID",           2,    null );
insert into ExtendAStoryVariable values( "NextEpisodeID",        2,    null );
insert into ExtendAStoryVariable values( "NextLinkID",           1,    null );
insert into ExtendAStoryVariable values( "NextEpisodeEditLogID", 1,    null );
insert into ExtendAStoryVariable values( "NextLinkEditLogID",    1,    null );
insert into ExtendAStoryVariable values( "NextSchemeID",         3,    null );
insert into ExtendAStoryVariable values( "NextImageID",          1,    null );

insert into ExtendAStoryVariable values( "StoryName",            null, "-"  );
insert into ExtendAStoryVariable values( "SiteName",             null, "-"  );
insert into ExtendAStoryVariable values( "StoryHome",            null, "-"  );
insert into ExtendAStoryVariable values( "SiteHome",             null, "-"  );
insert into ExtendAStoryVariable values( "ReadEpisodeURL",       null, "-"  );
insert into ExtendAStoryVariable values( "AdminEmail",           null, "-"  );
insert into ExtendAStoryVariable values( "IsWriteable",          null, "N"  );
insert into ExtendAStoryVariable values( "MaxLinks",             10,   null );
insert into ExtendAStoryVariable values( "MaxEditDays",          30,   null );

insert into User values( 1, 4, "admin", password( "change-me" ), "Administrator" );

insert into Episode values( 1, 1, 0, 0, 1, 0, 0, "N", "N", "N", "N", "-", "-", "-", "-", "-", "-", 0, null );

insert into Scheme values( 1, "Black Text on White Background",  "#FFFFFF", "#000000", "#0000FF", "#FF0000", "#00FF00", "",                  "images/red.gif",             "images/green.gif",         "images/blue.gif"             );
insert into Scheme values( 2, "White Text on Black Background",  "#000000", "#FFFFFF", "#00FF00", "#FF0000", "#0000FF", "",                  "images/red.gif",             "images/green.gif",         "images/blue.gif"             );
