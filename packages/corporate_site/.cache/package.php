<?php
// Automatically created cache file for the package format
// Do not modify this file

$CacheCodeDate = 1061114927;

$Parameters = array( "name" => "corporate_site",
                     "summary" => "Corporate website",
                     "description" => false,
                     "vendor" => false,
                     "priority" => false,
                     "type" => false,
                     "extension" => false,
                     "ezpublish" => array( "version" => "3.2.0-1",
                                           "named-version" => "3.2 beta1" ),
                     "maintainers" => array( array( "name" => "Anonymous User",
                                                    "email" => "nospam@ez.no",
                                                    "role" => "lead",
                                                    "modified" => "1061297970" ) ),
                     "packaging" => array( "timestamp" => "1061297970",
                                           "host" => "bf.ez.no",
                                           "packager" => false ),
                     "source" => false,
                     "documents" => array( array( "name" => "README",
                                                  "mime-type" => "text/plain",
                                                  "os" => false,
                                                  "audience" => false,
                                                  "modified" => "1061297970" ) ),
                     "groups" => array(),
                     "changelog" => array( array( "timestamp" => "1061297970",
                                                  "person" => "Anonymous User",
                                                  "email" => "nospam@ez.no",
                                                  "changes" => array( "Creation of package" ),
                                                  "release" => "1",
                                                  "modified" => "1061297970" ) ),
                     "file-list" => array( "default" => array( array( "name" => false,
                                                                      "subdirectory" => false,
                                                                      "type" => "design",
                                                                      "role" => false,
                                                                      "role-value" => false,
                                                                      "variable-name" => false,
                                                                      "path" => false,
                                                                      "file-type" => false,
                                                                      "design" => "corporate",
                                                                      "copy-file" => false,
                                                                      "modified" => "1061297970",
                                                                      "md5" => false ),
                                                               array( "name" => "override.ini.append",
                                                                      "subdirectory" => false,
                                                                      "type" => "ini",
                                                                      "role" => "siteaccess",
                                                                      "role-value" => "corporate",
                                                                      "variable-name" => "user_siteaccess",
                                                                      "path" => "settings/siteaccess/corporate/override.ini.append",
                                                                      "file-type" => false,
                                                                      "design" => false,
                                                                      "copy-file" => false,
                                                                      "modified" => "1061297971",
                                                                      "md5" => "0c778a9edb88685106f4eb37e8be11a4" ),
                                                               array( "name" => "site.ini.append",
                                                                      "subdirectory" => false,
                                                                      "type" => "ini",
                                                                      "role" => "siteaccess",
                                                                      "role-value" => "corporate",
                                                                      "variable-name" => "user_siteaccess",
                                                                      "path" => "settings/siteaccess/corporate/site.ini.append",
                                                                      "file-type" => false,
                                                                      "design" => false,
                                                                      "copy-file" => false,
                                                                      "modified" => "1061297972",
                                                                      "md5" => "695bcd8c5d4856a93e115a164b2be303" ),
                                                               array( "name" => "override.ini.append",
                                                                      "subdirectory" => false,
                                                                      "type" => "ini",
                                                                      "role" => "siteaccess",
                                                                      "role-value" => "admin",
                                                                      "variable-name" => "admin_siteaccess",
                                                                      "path" => "settings/siteaccess/admin/override.ini.append",
                                                                      "file-type" => false,
                                                                      "design" => false,
                                                                      "copy-file" => false,
                                                                      "modified" => "1061297973",
                                                                      "md5" => "50f4805254652ccea3c0191a23ce5c21" ),
                                                               array( "name" => "site.ini.append",
                                                                      "subdirectory" => false,
                                                                      "type" => "ini",
                                                                      "role" => "siteaccess",
                                                                      "role-value" => "admin",
                                                                      "variable-name" => "admin_siteaccess",
                                                                      "path" => "settings/siteaccess/admin/site.ini.append",
                                                                      "file-type" => false,
                                                                      "design" => false,
                                                                      "copy-file" => false,
                                                                      "modified" => "1061297973",
                                                                      "md5" => "47a0ba3f8010fba23cc881208bf4bfc1" ),
                                                               array( "name" => "thumbnail.png",
                                                                      "subdirectory" => false,
                                                                      "type" => "thumbnail",
                                                                      "role" => false,
                                                                      "role-value" => false,
                                                                      "variable-name" => false,
                                                                      "path" => "corporatethumbnail.png",
                                                                      "file-type" => false,
                                                                      "design" => false,
                                                                      "copy-file" => false,
                                                                      "modified" => "1061297976",
                                                                      "md5" => "b0d972c7ccd99c9ff1f2db8c67b38522" ) ) ),
                     "version-number" => "1.0",
                     "release-number" => "1",
                     "release-timestamp" => false,
                     "licence" => "GPL",
                     "state" => "alpha",
                     "dependencies" => array( "provides" => array( array( "type" => "ezfile",
                                                                          "name" => "collection",
                                                                          "value" => "default",
                                                                          "modified" => "1061297970" ) ),
                                              "requires" => array( array( "type" => "ezdb",
                                                                          "name" => "mysql",
                                                                          "value" => false,
                                                                          "modified" => "1061297974" ),
                                                                   array( "type" => "ezdb",
                                                                          "name" => "postgresql",
                                                                          "value" => false,
                                                                          "modified" => "1061297975" ) ),
                                              "obsoletes" => array(),
                                              "conflicts" => array() ),
                     "install" => array( array( "collection" => "default",
                                                "type" => "ezfile",
                                                "name" => false,
                                                "modified" => "1061297970",
                                                "os" => false,
                                                "filename" => false,
                                                "sub-directory" => false ),
                                         array( "path" => "packages/corporate.sql",
                                                "database-type" => "mysql",
                                                "type" => "sql",
                                                "name" => false,
                                                "modified" => "1061297974",
                                                "os" => false,
                                                "filename" => "corporate.sql",
                                                "sub-directory" => false ),
                                         array( "path" => "packages/corporate_postgresql.sql",
                                                "database-type" => "postgresql",
                                                "type" => "sql",
                                                "name" => false,
                                                "modified" => "1061297975",
                                                "os" => false,
                                                "filename" => "corporate_postgresql.sql",
                                                "sub-directory" => false ) ),
                     "uninstall" => array( array( "collection" => "default",
                                                  "type" => "ezfile",
                                                  "name" => false,
                                                  "modified" => "1061297970",
                                                  "os" => false,
                                                  "filename" => false,
                                                  "sub-directory" => false ) ) );
$ModifiedParameters = array( "name" => "1061297970",
                             "version-number" => "1061297970",
                             "release-number" => "1061297970",
                             "licence" => "1061297970",
                             "state" => "1061297970",
                             "summary" => 1061297976 );
$RepositoryPath = "packages";
?>
