[![devDependencies Status](https://david-dm.org/wikimedia/mediawiki-extensions-UserExport/dev-status.svg)](https://david-dm.org/wikimedia/mediawiki-extensions-UserExport?type=dev)

## UserExport

The UserExport extension to MediaWiki allows to export all user's account names together with the respective
e-mail address as a UTF-8 encoded CSV file.


### Compatibility

* PHP 5.4+
* MediaWiki 1.25+

See also the CHANGELOG.md file provided with the code.


### Installation

1. Obtain the code from [GitHub](https://github.com/wikimedia/mediawiki-extensions-UserExport/releases)
2. Extract the files in a directory called `UserExport` in your `extensions/` folder.
3. Add the following code at the bottom of your "LocalSettings.php" file:  
```
wfLoadExtension( 'UserExport' );
```
4. Configure as required. See the "Configuration" section below.
5. Go to "Special:Version" on your wiki to verify that the extension was successfully installed.
6. Done.


### Configuration

By default the `userexport` permission provided by this extension is assigned to any user group. In case you
would like to assign the permission to an existing user group, e.g. "bureaucrat" add the following line to your
"LocalSettings.php" file after the inclusion of the extension as described in the "Installation" section above:

```
$wgGroupPermissions['bureaucrat']['userexport'] = true;
```

Preferably you could assing the permission to a new user group, e.g. "userexport" to allow for a more fine graned
permission management by adding the following line instead:

```
$wgGroupPermissions['userexport']['userexport'] = true;
```


### Usage
See the [extension's homepage](https://www.mediawiki.org/wiki/Extension:UserExport) for further information.
