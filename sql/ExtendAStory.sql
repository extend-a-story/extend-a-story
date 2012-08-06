# Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
# Copyright (C) 2002 - 2012  Jeffrey J. Weston, Matthew Duhan
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


DROP TABLE IF EXISTS ExtendAStoryVariable;
DROP TABLE IF EXISTS Session;
DROP TABLE IF EXISTS User;
DROP TABLE IF EXISTS Episode;
DROP TABLE IF EXISTS Link;
DROP TABLE IF EXISTS EpisodeEditLog;
DROP TABLE IF EXISTS LinkEditLog;
DROP TABLE IF EXISTS Scheme;
DROP TABLE IF EXISTS Image;


CREATE TABLE ExtendAStoryVariable
(
    VariableName  VARCHAR( 255 )  NOT NULL  PRIMARY KEY,
    IntValue      INT UNSIGNED    NULL,
    StringValue   VARCHAR( 255 )  NULL
);


CREATE TABLE Session
(
    SessionID   INT UNSIGNED  NOT NULL  PRIMARY KEY  AUTO_INCREMENT,
    UserID      INT UNSIGNED  NOT NULL,
    SessionKey  INT UNSIGNED  NOT NULL,
    AccessDate  DATETIME      NOT NULL
);


CREATE TABLE User
(
    UserID           INT     UNSIGNED  NOT NULL  PRIMARY KEY  AUTO_INCREMENT,
    PermissionLevel  TINYINT UNSIGNED  NOT NULL,
    LoginName        VARCHAR( 255 )    NOT NULL,
    Password         VARCHAR( 255 )    NOT NULL,
    UserName         VARCHAR( 255 )    NOT NULL
);


# Description of PermissionLevel values:
#   1 - Moderator       : Can edit any episode.
#   2 - Super Moderator : As above, but can also modify link structure.
#   3 - Manager         : As above, but can also change configuration values.
#   4 - Administrator   : As above, but can also edit users.


CREATE TABLE Episode
(
    EpisodeID          INT     UNSIGNED  NOT NULL  PRIMARY KEY  AUTO_INCREMENT,
    Parent             INT     UNSIGNED  NOT NULL,
    AuthorSessionID    INT     UNSIGNED  NOT NULL,
    EditorSessionID    INT     UNSIGNED  NOT NULL,
    SchemeID           INT     UNSIGNED  NOT NULL,
    ImageID            INT     UNSIGNED  NOT NULL,
    Status             TINYINT UNSIGNED  NOT NULL,
    IsLinkable         CHAR   ( 1   )    NOT NULL,
    IsExtendable       CHAR   ( 1   )    NOT NULL,
    AuthorMailto       CHAR   ( 1   )    NOT NULL,
    AuthorNotify       CHAR   ( 1   )    NOT NULL,
    Title              VARCHAR( 255 )    NOT NULL,
    Text               TEXT              NOT NULL,
    AuthorName         VARCHAR( 255 )    NOT NULL,
    AuthorEmail        VARCHAR( 255 )    NOT NULL,
    CreationDate       VARCHAR( 255 )    NOT NULL,
    LockDate           VARCHAR( 255 )    NOT NULL,
    LockKey            INT     UNSIGNED  NOT NULL,
    CreationTimestamp  DATETIME          NULL
);


# Description of Status values:
#   0 - Not Created
#   1 - Not Created / Locked for Creation
#   2 - Created
#   3 - Created / Locked for Editing


CREATE TABLE Link
(
    LinkID          INT UNSIGNED    NOT NULL  PRIMARY KEY,
    SourceEpisodeID INT UNSIGNED    NOT NULL,
    TargetEpisodeID INT UNSIGNED    NOT NULL,
    IsCreated       CHAR   ( 1   )  NOT NULL,
    IsBackLink      CHAR   ( 1   )  NOT NULL,
    Description     VARCHAR( 255 )  NOT NULL,
    INDEX( SourceEpisodeID ),
    INDEX( TargetEpisodeID )
);


CREATE TABLE EpisodeEditLog
(
    EpisodeEditLogID  INT UNSIGNED    NOT NULL  PRIMARY KEY,
    EpisodeID         INT UNSIGNED    NOT NULL,
    SchemeID          INT UNSIGNED    NOT NULL,
    ImageID           INT UNSIGNED    NOT NULL,
    IsLinkable        CHAR   ( 1   )  NOT NULL,
    IsExtendable      CHAR   ( 1   )  NOT NULL,
    AuthorMailto      CHAR   ( 1   )  NOT NULL,
    AuthorNotify      CHAR   ( 1   )  NOT NULL,
    Title             VARCHAR( 255 )  NOT NULL,
    Text              TEXT            NOT NULL,
    AuthorName        VARCHAR( 255 )  NOT NULL,
    AuthorEmail       VARCHAR( 255 )  NOT NULL,
    EditDate          VARCHAR( 255 )  NOT NULL,
    EditLogEntry      VARCHAR( 255 )  NOT NULL,
    INDEX( EpisodeID )
);


CREATE TABLE LinkEditLog
(
    LinkEditLogID     INT UNSIGNED    NOT NULL  PRIMARY KEY,
    EpisodeEditLogID  INT UNSIGNED    NOT NULL,
    TargetEpisodeID   INT UNSIGNED    NOT NULL,
    IsBackLink        CHAR   ( 1   )  NOT NULL,
    Description       VARCHAR( 255 )  NOT NULL,
    INDEX( EpisodeEditLogID )
);


CREATE TABLE Scheme
(
    SchemeID        INT UNSIGNED    NOT NULL  PRIMARY KEY,
    SchemeName      VARCHAR( 255 )  NOT NULL,
    bgcolor         VARCHAR( 255 )  NOT NULL,
    text            VARCHAR( 255 )  NOT NULL,
    link            VARCHAR( 255 )  NOT NULL,
    vlink           VARCHAR( 255 )  NOT NULL,
    alink           VARCHAR( 255 )  NOT NULL,
    background      VARCHAR( 255 )  NOT NULL,
    UncreatedLink   VARCHAR( 255 )  NOT NULL,
    CreatedLink     VARCHAR( 255 )  NOT NULL,
    BackLinkedLink  VARCHAR( 255 )  NOT NULL
);


CREATE TABLE Image
(
    ImageID    INT UNSIGNED    NOT NULL  PRIMARY KEY,
    ImageName  VARCHAR( 255 )  NOT NULL,
    ImageURL   VARCHAR( 255 )  NOT NULL
);


INSERT INTO ExtendAStoryVariable VALUES ( "CountDate",            NULL, date_format( now(), '%c/%e/%Y %l:%i:%s %p' ));
INSERT INTO ExtendAStoryVariable VALUES ( "CountValue",           0,    NULL );
INSERT INTO ExtendAStoryVariable VALUES ( "NextEpisodeEditLogID", 1,    NULL );
INSERT INTO ExtendAStoryVariable VALUES ( "NextLinkEditLogID",    1,    NULL );
INSERT INTO ExtendAStoryVariable VALUES ( "NextSchemeID",         3,    NULL );
INSERT INTO ExtendAStoryVariable VALUES ( "NextImageID",          1,    NULL );

INSERT INTO ExtendAStoryVariable VALUES ( "StoryName",            NULL, "-"  );
INSERT INTO ExtendAStoryVariable VALUES ( "SiteName",             NULL, "-"  );
INSERT INTO ExtendAStoryVariable VALUES ( "StoryHome",            NULL, "-"  );
INSERT INTO ExtendAStoryVariable VALUES ( "SiteHome",             NULL, "-"  );
INSERT INTO ExtendAStoryVariable VALUES ( "ReadEpisodeURL",       NULL, "-"  );
INSERT INTO ExtendAStoryVariable VALUES ( "AdminEmail",           NULL, "-"  );
INSERT INTO ExtendAStoryVariable VALUES ( "IsWriteable",          NULL, "N"  );
INSERT INTO ExtendAStoryVariable VALUES ( "MaxLinks",             10,   NULL );
INSERT INTO ExtendAStoryVariable VALUES ( "MaxEditDays",          30,   NULL );


INSERT INTO User
            (
                PermissionLevel,
                LoginName,
                Password,
                UserName
            )
     VALUES
            (
                4,
                "admin",
                PASSWORD( "change-me" ),
                "Administrator"
            );


INSERT INTO Episode
            (
                Parent,
                AuthorSessionID,
                EditorSessionID,
                SchemeID,
                ImageID,
                Status,
                IsLinkable,
                IsExtendable,
                AuthorMailto,
                AuthorNotify,
                Title,
                Text,
                AuthorName,
                AuthorEmail,
                CreationDate,
                LockDate,
                LockKey,
                CreationTimestamp
            )
     VALUES
            (
                1,
                0,
                0,
                1,
                0,
                0,
                "N",
                "N",
                "N",
                "N",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                0,
                NULL
            );


INSERT INTO Scheme VALUES ( 1, "Black Text on White Background", "#FFFFFF", "#000000", "#0000FF", "#FF0000", "#00FF00", "", "images/red.gif", "images/green.gif", "images/blue.gif" );
INSERT INTO Scheme VALUES ( 2, "White Text on Black Background", "#000000", "#FFFFFF", "#00FF00", "#FF0000", "#0000FF", "", "images/red.gif", "images/green.gif", "images/blue.gif" );
