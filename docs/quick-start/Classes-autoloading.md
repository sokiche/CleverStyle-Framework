### System autoloader
System manages autoloading classes of system and components (also system traits).

Autoloader look for classes/traits within namespace `cs` in next directories:
* `core/classes`
* `core/engines`
* `core/traits`

Subnamespaces are converted into subdirectories, for instance `cs\Cache\FileSystem` class is located in `core/engines/Cache/FileSystem.php`

Also autoloader looks for classes inside
* `core/classes/thirdparty`

for some thirdparty components.

Classes in namespaces `cs\modules` and `cs\plugins` are used by modules and plugins correspondingly:
* `cs\modules\Blogs\Posts` > `components/modules/Blogs/Posts.php`
* `cs\modules\Comments\Comments` > `components/modules/Comments/Comments.php`

### Composer
User's `composer.json` can be in root directory.
By default this file is absent, but you can create such file, and after running `composer.phar install` composer's autoloader will be used automatically.
Custom `composer.json` can be used to create custom builds, because this file (if exists) will be included in distributive automatically.