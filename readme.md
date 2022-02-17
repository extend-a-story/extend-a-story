# Extend-A-Story

Extend-A-Story is an interactive, extendable, choose-your-own-adventure story.
It is written in PHP and uses a MySQL database.

## Under Development

**This version of Extend-A-Story is under development and may not be stable or work correctly.**

Download a stable version of Extend-A-Story from our tags page:

[Extend-A-Story Tags](https://github.com/extend-a-story/extend-a-story/tags)

## Documentation

- [Install Extend-A-Story](install.md)
- [Upgrade Extend-A-Story](upgrade.md)
- [Extend-A-Story Release Notes](release-notes.md)

## Database Maintenance

Although I've tried my best to ensure that Extend-A-Story is bug free,
some inconsistencies may still crop up in your database.
Some examples of problems you may find are:

- A link record has an incorrect 'IsCreated' flag, where the episode it points to is created,
but the link says it's not, or the episode it points to is not created, but the link says it is.
- A link record has an incorrect 'IsBackLink' flag, where the target episode is an actual child of the source episode,
but the link says it's not, or the target is not a child of the source episode, but the link says it is.

I've included SQL statements that will identify these problems in: [sql/DBMaintenance.sql](sql/DBMaintenance.sql)
