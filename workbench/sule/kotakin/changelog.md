#v1.2.5

 - Reduce saved file name to 40 chars.

#v1.2.4

 - Fixing login link sent to new registered user.

#v1.2.3

 - Fixing link expire time checking when no time defined.
 - Fixing folder download.
 - Provide default user background image if not defined.

#v1.2.2

 - Fixing link expire time checking.
 - Disable the disk space indicator when the PHP function is not available / disabled.
 - Fixing the downloader process, to make sure it still working when PHP fileinfo module is not available.

#v1.2.1

 - Fixing changing password.

#v1.2.0

 - Option to disable users upload in shared folder.
 - MP3 Preview (using http://kolber.github.io/audiojs/).
 - MP4 Video Preview (using http://www.videojs.com).
 - Fixing moving folder & file to shared folder.
 - Modified HTML Files:
   -- workbench/sule/kotakin/src/views/admin_layout.
   -- workbench/sule/kotakin/src/views/admin_dashboard.
   -- workbench/sule/kotakin/src/views/user_layout.
   -- workbench/sule/kotakin/src/views/user_dashboard.
 - Texts added:
   -- %s of %s, %s%s free%s
   -- Allow Upload?
   -- Enable
   -- Disable
   -- Uploading file is disabled in this folder
   -- Unable to move "%s" to "%s", please make sure no same folder (with uppercase or lower case name) there.

#v1.1.0

 - Show disk usage capacity at admin dashboard.
 - Add artisan command "kotakin:removeExpiredLink" to remove any expired link, and the file (optional).
 - Add artisan command "kotakin:removeDlLimitedLink" to remove any download limit reached link, and the file (optional).