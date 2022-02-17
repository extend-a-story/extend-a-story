# Upgrade Extend-A-Story

Follow these steps to upgrade from a previous version of Extend-A-Story to this version.

## Disable Episode Creation

Consider disabling episode creation before upgrading to prevent changes to your database during the process.
How to do this depends on your current version of Extend-A-Story.

### Versions 2.1.0 and Later

Versions 2.1.0 and later have a web interface for disabling episode creation.

Use your web browser to open `admin.php` in the `<story-root>` directory.
Log in using an account with the `Manager` or `Administrator` permission level.
Click `Configure Story Settings`.
Set `Is Writeable` to `No` and click `Save`.

### Versions Before 2.1.0

Versions before 2.1.0 do not have a web interface for disabling episode creation.
You must make this change directly in your database instead.

Connect to your database and execute the following statement:

```SQL
UPDATE ExtendAStoryVariable SET StringValue = "N" WHERE VariableName = "IsWriteable";
```

## Perform Backup

We recommend performing a backup of your database and `<story-root>` directory before upgrading.
If anything goes wrong with the upgrade you can use these backups to restore your current version.

## Preserve Page footer

If you previously configured a page footer for Extend-A-Story
preserve the footer file so that you can restore it after upgrading:

`<story-root>/include/config/Footer.php`

## Note Database Connection Settings

Make a note of your current database connection settings in your Extend-A-Story configuration file:

`<story-root>/db.php`

The location and content of the Extend-A-Story configuration file is different in this version.
The upgrade process will guide you through setting up your configuration file for this version.

## Upgrade Story Root Directory

Extend-A-Story is installed in a directory that your web server can access.
We refer to this directory as the `<story-root>` directory.

Replace the contents of the `<story-root>` directory with the contents of the `www` directory for this version.

## Run Upgrade Process

Use your web browser to open the install page, `install.php`, in the `<story-root>` directory.
The install page will guide you through the upgrade process.
You will need the database connection settings that we referenced above.

After you specify your database connection settings you will be prompted for which task you wish to perform.
Be sure to select `Upgrade Existing Database`, otherwise all data in your database will be deleted.

You may be asked for additional information to complete the upgrade
depending on which version of Extend-A-Story you are upgrading from.

## Restore Page footer

If you previously configured a page footer for Extend-A-Story
be sure to restore the footer file that you preserved earlier:

`<story-root>/include/config/Footer.php`

## Enable Episode Creation

If you disabled episode creation before starting the upgrade
be sure to enable it again once the upgrade is complete.

Use your web browser to open `admin.php` in the `<story-root>` directory.
Log in using an account with the `Manager` or `Administrator` permission level.
Click `Configure Story Settings`.
Set `Is Writeable` to `Yes` and click `Save`.
