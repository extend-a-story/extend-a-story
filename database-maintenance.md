# Extend-A-Story Database Maintenance

Although I've tried my best to ensure that Extend-A-Story is bug free,
some inconsistencies may still crop up in your database.
Some examples of problems you may find are:

- A link record has an incorrect 'IsCreated' flag, where the episode it points to is created,
but the link says it's not, or the episode it points to is not created, but the link says it is.
- A link record has an incorrect 'IsBackLink' flag, where the target episode is an actual child of the source episode,
but the link says it's not, or the target is not a child of the source episode, but the link says it is.

I've included SQL statements that will identify these problems in: [sql/DBMaintenance.sql](sql/DBMaintenance.sql)
