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
