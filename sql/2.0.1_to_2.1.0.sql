# Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
# Copyright (C) 2002-2019 Jeffrey J. Weston <jjweston@gmail.com>
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


CREATE TABLE User
(
    UserID           INT     UNSIGNED  NOT NULL  PRIMARY KEY,
    PermissionLevel  TINYINT UNSIGNED  NOT NULL,
    LoginName        VARCHAR( 255 )    NOT NULL,
    Password         CHAR   ( 16  )    NOT NULL,
    UserName         VARCHAR( 255 )    NOT NULL
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


ALTER TABLE Session ADD COLUMN UserID  INT UNSIGNED  NOT NULL  AFTER SessionID;


INSERT INTO ExtendAStoryVariable VALUES ( "NextUserID",           2,  NULL );
INSERT INTO ExtendAStoryVariable VALUES ( "NextEpisodeEditLogID", 1,  NULL );
INSERT INTO ExtendAStoryVariable VALUES ( "NextLinkEditLogID",    1,  NULL );
INSERT INTO ExtendAStoryVariable VALUES ( "MaxEditDays",          30, NULL );


INSERT INTO User VALUES ( 1, 4, "admin", PASSWORD( "change-me" ), "Administrator" );
