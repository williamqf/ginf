# OcoMon - version 6.x
## Date: December 2024 - Last update: June 2025
## Author: Flávio Ribeiro (flaviorib@gmail.com)

## License: GPLv3


## IMPORTANT:

If you wish to install OcoMon on your own, you need to know what a WEB server is and be familiar with the generic process of installing WEB systems.

To install OcoMon, you need an account with database creation permissions in MySQL or MariaDB and write access to your web server's public folder.

Before starting the installation or update process, **read this file to the end.**


## REQUIREMENTS:

+ Web server with Apache(***not tested with other servers***), PHP and MySQL (or MariaDB):
    
    - MySQL version **5.7** or higher (Or MariaDB version **10.2** or higher)
    - PHP version **8.1** or higher with:
        - PDO
        - pdo_mysql
        - mbstring
        - openssl
        - imap
        - curl
        - iconv
        - gd
        - fileinfo
        - ldap (if authenticating via LDAP)

    - For using the API for integration or to enable ticket creation via email:
        - Apache needs to allow URL rewriting (to route API routes via htaccess)
        - The "mod_rewrite" module needs to be enabled in Apache
        - Apache needs write permission to the "api/ocomon_api/storage" folder.

<br>

## INSTALLATION OR UPDATE IN PRODUCTION ENVIRONMENT:


#### IMPORTANT (in case of update)

+ It is strongly **recommended** to **BACKUP** your database! **Do this first** and avoid potential headaches.

+ Identify what **your installed version** is. After that, go directly to the section in this document corresponding to the specific update instructions for your version. For each OcoMon version, there is **only ONE specific SQL file** (or none) to be imported into your database.

+ Check the version updates at [https://ocomon.com.br/site/changelog-incremental/](https://ocomon.com.br/site/changelog-incremental/) to identify new usage possibilities and new configurations.


### Version Update:


#### If your current version is "6.0 - (PREVIEW TO SUPPORTERS)":

Some users had access to version 6 preview before the official release. If this is your case, contact ocomon.oficial@gmail.com to assess if it's necessary to update your database (depending on the version series, it may be necessary to update the database).


### If your current version is one of the following versions: 6.1 OR 6.02:

No database update is necessary. To update, simply overwrite your version's scripts with version **6.3** scripts.


#### If your current version is one of the following versions 6.0, 6.0.1:

**IMPORTANT:** It is always recommended to **BACKUP** both the scripts of the version in use and the database currently in use by the system.

**IMPORTANT 2:** If your version has update history from older versions, it may be necessary to update the engine of some tables that are in "MYISAM" to the "InnoDB" engine. Therefore, **before starting the update process**, check the engines of OcoMon tables to make sure they are ok.

1. Import the database update file "00-DB-UPDATE_FROM_6.0.1.sql" (in install/6.x/): <br>

    Example via command line:
```shell
mysql -u root -p [database_name] < /path_to_ocomon_6.x/install/6.x/00-DB-UPDATE_FROM_6.0.1.sql
```
Where: [database_name]: Is the name of OcoMon's database

PS: If you prefer, you can use a database manager like phpMyAdmin, for example, to import the SQL script.

2. Overwrite your version's scripts with version 6.0.2 scripts (recommended: keep only your "config.inc.php" configuration file and remove (or move to a backup directory) all other scripts);

3. For security reasons, after importing the SQL, remove the install folder. Done! Just adjust the new version configurations directly via administrative interface.



#### If your current version is from series 5.x

**IMPORTANT:** It is always recommended to **BACKUP** both the scripts of the version in use and the database currently in use by the system.

**IMPORTANT 2:** If your version has update history from older versions, it may be necessary to update the engine of some tables that are in "MYISAM" to the "InnoDB" engine. Therefore, **before starting the update process**, check the engines of OcoMon tables to make sure they are ok.

1. Import the database update file "02-UPDATE_FROM_5.x.sql" (in install/6.x/): <br>

    Example via command line:
```shell
mysql -u root -p [database_name] < /path_to_ocomon_6.x/install/6.x/02-UPDATE_FROM_5.x.sql
```
Where: [database_name]: Is the name of OcoMon's database

PS: If you prefer, you can use a database manager like phpMyAdmin, for example, to import the SQL script.

2. Overwrite your version's scripts with version 6 scripts (recommended: keep only your "config.inc.php" configuration file and remove (or move to a backup directory) all other scripts);

3. For security reasons, after importing the SQL, remove the install folder. Done! Just adjust the new version configurations directly via administrative interface.


#### If your current version is from 4.x series:

**IMPORTANT:** It is always recommended to **BACKUP** both the scripts of the version in use and the database currently being used by the system.

**IMPORTANT 2:** If your version has an update history from older versions, it may be necessary to update the engine of some tables that are in "MYISAM" to the "InnoDB" engine. Therefore, **before starting the update process**, check the engines of the OcoMon tables to make sure they are ok.

1. Import the database update file "03-UPDATE_FROM_4.x.sql" (located in install/6.x/): <br>

    Ex. via command line:
```shell
mysql -u root -p [database_name] < /path_to_ocomon_6.x/install/6.x/03-UPDATE_FROM_4.x.sql
```

Where: [database_name] is the name of the OcoMon database

PS: If you prefer, you can use a database manager like phpMyAdmin to import the SQL script.

2. Overwrite your version scripts with version 6 scripts (recommended: keep only your configuration file "config.inc.php" and remove (or move to a backup directory) all other scripts);

3. For security reasons, after importing the SQL, remove the install folder. Done! Just adjust the new version settings directly through the administrative interface.<br>


#### If your current version is 3.3:

**IMPORTANT:** It is always recommended to **BACKUP** both the scripts of the version in use and the database currently being used by the system.

**IMPORTANT 2:** If your version has an update history from older versions, it may be necessary to update the engine of some tables that are in "MYISAM" to the "InnoDB" engine. Therefore, **before starting the update process**, check the engines of the OcoMon tables to make sure they are ok.

1. Import the database update file "04-DB-UPDATE-FROM-3.3.sql" (located in install/6.x/): <br>

    Ex. via command line:
```shell
mysql -u root -p [database_name] < /path_to_ocomon_6.x/install/6.x/04-DB-UPDATE-FROM-3.3.sql
```

Where: [database_name] is the name of the OcoMon database

PS: If you prefer, you can use a database manager like phpMyAdmin to import the SQL script.

2. Overwrite your version scripts with version 6 scripts (recommended: keep only your configuration file "config.inc.php" and remove (or move to a backup directory) all other scripts);

3. For security reasons, after importing the SQL, remove the install folder. Done! Just adjust the new version settings directly through the administrative interface.<br>


#### If your current version is 3.2 or 3.1 or 3.1.1:

**IMPORTANT:** It is always recommended to **BACKUP** both the scripts of the version in use and the database currently being used by the system.

**IMPORTANT 2:** If your version has an update history from older versions, it may be necessary to update the engine of some tables that are in "MYISAM" to the "InnoDB" engine. Therefore, **before starting the update process**, check the engines of the OcoMon tables to make sure they are ok.

1. Import the database update file "05-DB-UPDATE-FROM-3.2.sql" (located in install/6.x/): <br>

    Ex. via command line:
```shell
mysql -u root -p [database_name] < /path_to_ocomon_6.x/install/6.x/05-DB-UPDATE-FROM-3.2.sql
```

Where: [database_name] is the name of the OcoMon database

PS: If you prefer, you can use a database manager like phpMyAdmin to import the SQL script.

2. Overwrite your version scripts with version 6 scripts (recommended: keep only your configuration file "config.inc.php" and remove (or move to a backup directory) all other scripts);

3. For security reasons, after importing the SQL, remove the install folder. Done! Just adjust the new version settings directly through the administrative interface.


#### If your current version is 3.0 (final release):

**IMPORTANT:** It is always recommended to **BACKUP** both the scripts of the version in use and the database currently being used by the system.

**IMPORTANT 2:** If your version has an update history from older versions, it may be necessary to update the engine of some tables that are in "MYISAM" to the "InnoDB" engine. Therefore, **before starting the update process**, check the engines of the OcoMon tables to make sure they are ok.

1. Import the database update file "06-DB-UPDATE-FROM-3.0.sql" (located in install/6.x/): <br>

    Ex. via command line:
```shell
mysql -u root -p [database_name] < /path_to_ocomon_6.x/install/6.x/06-DB-UPDATE-FROM-3.0.sql
```

Where: [database_name] is the name of the OcoMon database

PS: If you prefer, you can use a database manager like phpMyAdmin to import the SQL script.

2. Overwrite your version scripts with version 6 scripts (recommended: keep only your configuration file "config.inc.php" and remove (or move to a backup directory) all other scripts);

3. For security reasons, after importing the SQL, remove the install folder. Done! Just adjust the new version settings directly through the administrative interface.<br>


#### If your current version is any of the release candidates (rc) of version 3.0 (rc1, rc2, rc3):

**IMPORTANT:** It is always recommended to **BACKUP** both the scripts of the version in use and the database currently being used by the system.

**IMPORTANT 2:** If your version has an update history from older versions, it may be necessary to update the engine of some tables that are in "MYISAM" to the "InnoDB" engine. Therefore, **before starting the update process**, check the engines of the OcoMon tables to make sure they are ok.

1. Import the database update file "07-DB-UPDATE-FROM-3.0rcx.sql" (located in install/6.x/): <br>

    Ex. via command line:
```shell
mysql -u root -p [database_name] < /path_to_ocomon_6.x/install/6.x/07-DB-UPDATE-FROM-3.0rcx.sql
```

Where: [database_name] is the name of the OcoMon database

PS: If you prefer, you can use a database manager like phpMyAdmin to import the SQL script.

2. Overwrite your version scripts with version 6 scripts (recommended: keep only your configuration file "config.inc.php" and remove (or move to a backup directory) all other scripts);

3. For security reasons, after importing the SQL, remove the install folder. Done! Just adjust the new version settings directly through the administrative interface.<br><br>


#### If your current version is 2.0 final (**Not version 2.0RC6**)

+ Make a **BACKUP** of both the scripts of the version in use and the database currently being used by the system.

- **IMPORTANT:** Keep in mind that OcoMon's 2.x series works with PHP 5 and the current version only works with **PHP version 8.1 or higher**. Therefore, the OcoMon environment will also need to be updated.

- **IMPORTANT 2:** Update the engine of all OcoMon tables that are using "MYISAM" to "InnoDB"; This change is essential before importing the update file.

+ **IMPORTANT 3:** Carefully read the changelog-3.0.md file (*in /changelog*) to check the main changes and especially about **functions removed from previous versions** and some new **required configurations**, as well as changes in return time for SLAs on pre-existing tickets.


+ The update process described below assumes that the current version is 2.0 (**final release**). If your version is 2.0RC6, go to the related section.

+ **IMPORTANT:** Depending on your database configuration regarding "case sensitive", you will need to rename the following tables (if they have nomenclature with uppercase "X"): "areaXarea_abrechamado", "equipXpieces" to: "areaxarea_abrechamado", "equipxpieces". This **MUST** be done **BEFORE** importing the SQL update file.

+ To update from version 2.0 (final release), simply overwrite the scripts in your OcoMon folder with the new version scripts (recommended: keep only your configuration file "config.inc.php" and remove (or move to a backup directory) all other scripts) and import the update file to MySQL: 07-DB-UPDATE-FROM-2.0.sql (in /install/5.x/). <br><br>
1. Import the database update file "08-DB-UPDATE-FROM-2.0.sql" (located in install/6.x/): <br>

    Ex. via command line:
```shell
mysql -u root -p [database_name] < /path_to_ocomon_6.x/install/6.x/08-DB-UPDATE-FROM-2.0.sql
```

Where: [database_name] is the name of the OcoMon database

PS: If you prefer, you can use a database manager like phpMyAdmin to import the SQL script.

2. Overwrite your version scripts with version 6 scripts (recommended: keep only your configuration file "config.inc.php" and remove (or move to a backup directory) all other scripts);

3. For security reasons, after importing the SQL, remove the install folder. Done! Just adjust the new version settings directly through the administrative interface.<br>

<br>

#### If your current version is version 2.0RC6

+ Make a **BACKUP** of both the scripts of the version in use and the database currently being used by the system.

- **IMPORTANT:** Keep in mind that OcoMon's 2.x series works with PHP 5 and the current version only works with **PHP version 8.1 or higher**. Therefore, the OcoMon environment will also need to be updated.

- **IMPORTANT 2:** Update the engine of all OcoMon tables that are using "MYISAM" to "InnoDB"; This change is essential before importing the update file.

+ **IMPORTANT 3:** Carefully read the changelog-3.0.md file (*in /changelog*) to check the main changes and especially about **functions removed from previous versions** and some new **required configurations**, as well as changes in return time for SLAs on pre-existing tickets.

+ The update process described below assumes that the current version is 2.0RC6 (**official version**). If your version has any customization, this **update action is not recommended**.

+ **IMPORTANT:** Depending on your database configuration regarding "case sensitive", you will need to rename the following tables (if they have nomenclature with uppercase "X"): "areaXarea_abrechamado", "equipXpieces" to: "areaxarea_abrechamado", "equipxpieces". This **MUST** be done **BEFORE** importing the SQL update file.

+ To update from version 2.0RC6, simply overwrite the scripts in your OcoMon folder with the new version scripts (recommended: keep only your configuration file "config.inc.php" and remove (or move to a backup directory) all other scripts) and import the update file to MySQL: 08-DB-UPDATE_FROM_2.0RC6.sql (in /install/5.x/). <br><br>
1. Import the database update file "09-DB-UPDATE_FROM_2.0RC6.sql" (located in install/6.x/): <br>

    Ex. via command line:
```shell
mysql -u root -p [database_name] < /path_to_ocomon_6.x/install/6.x/09-DB-UPDATE_FROM_2.0RC6.sql
```

Where: [database_name] is the name of the OcoMon database

PS: If you prefer, you can use a database manager like phpMyAdmin to import the SQL script.

2. Overwrite your version scripts with version 6 scripts (recommended: keep only your configuration file "config.inc.php" and remove (or move to a backup directory) all other scripts);

3. For security reasons, after importing the SQL, remove the install folder. Done! Just adjust the new version settings directly through the administrative interface.<br>

<br/><br/>
### First Installation:

The installation process is quite simple and can be done in 3 steps:

1. **Install system scripts:**

Extract the contents of the OcoMon_6x package to your web server's public directory (*the path may vary depending on the distribution or configuration, but generally is **/var/www/html/***).

File permissions can be the server defaults (except for the api/ocomon_api/storage folder, which needs write permission for the Apache user).

2. **Database creation:**<br>
#### LOCALLY HOSTED SYSTEM
(**localhost** - If the system will be installed on an external server, skip to the [EXTERNAL HOSTING SYSTEM] section):
    
To create the entire OcoMon database, you need to import a single SQL instructions file:
    
The file is:
`01-DB_OCOMON_6.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql (in /install/6.x/)`

Command line (if you have terminal access):

```shell
mysql -u root -p < /path_to_ocomon_6.x/install/6.x/01-DB_OCOMON_6.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql
```

The system will ask for the MySQL root user password (or any other user that has been provided instead of root in the command above).

The above command will create the user "ocomon_6" with the default password "senha_ocomon_mysql", and the database "ocomon_6" with all the information needed to initialize the system.

**It is important to change this password for the "ocomon_6" user in MySQL right after installing the system.**

If you prefer, you can import the SQL file using any database manager of your choice; in this case, you don't need to use the terminal.

If you want the database and/or user to have a different name (instead of "ocomon_6"), edit directly in the file (*identify the entries related to database name, user, and password at the beginning of the file*):

`01-DB_OCOMON_6.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql`

before importing it. Use this same information in the system configuration file (step **3**).
    
**After importing, it is recommended to delete the "install" folder.**<br><br>


#### EXTERNAL HOSTING SYSTEM:

If the system will be installed on an external server, in this case, due to possible creation limitations for database and user nomenclature (usually the provider stipulates a prefix for databases and users), it is recommended to use the username provided by the hosting service itself or create a specific user (if your user account allows) directly through your database access interface. Therefore:

- **Create** a specific database for OcoMon (you define the name);
- **Create** a specific user for accessing the OcoMon database (or use your default user);
- **Modify** the "01-DB_OCOMON_6.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql" script by **removing** the following lines from the beginning of the file:

```sql
CREATE DATABASE /*!32312 IF NOT EXISTS*/`ocomon_6` /*!40100 DEFAULT CHARACTER SET utf8 */;

CREATE USER 'ocomon_6'@'localhost' IDENTIFIED BY 'senha_ocomon_mysql';
GRANT SELECT , INSERT , UPDATE , DELETE ON `ocomon_6` . * TO 'ocomon_6'@'localhost';
GRANT Drop ON ocomon_6.* TO 'ocomon_6'@'localhost';
FLUSH PRIVILEGES;

USE `ocomon_6`;
```

After that, simply import the modified file and continue with the installation process.

1. If you have terminal access:

```shell
mysql -u root -p [database_name] < /path_to_ocomon_6.x/install/6.x/01-DB_OCOMON_6.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql
```

Where: [database_name] is the name of the database that was manually created.<br>

If you prefer, you can use a database manager like phpMyAdmin to import the SQL script.

2. Overwrite your version scripts with version 6 scripts (recommended: keep only your configuration file "config.inc.php" and remove (or move to a backup directory) all other scripts);

3. Copy the config.inc.php-Dist (*/includes/*) file and rename to config.in.php. In this new file, check and review information related to the database connection (*server, database, user and password*).

For security reasons, after importing the SQL file, remove the install folder. Done! Just adjust the new version settings directly through the administrative interface.



## TEST VERSION:

If you want to test the system before installing, you can run a Docker container with the system already working with some populated data. If you already have Docker installed in your environment, just run the following command in your terminal:

```shell
docker run -it -p 8080:80 flaviorib/ocomon:6.0 /start_ocomon
```

Then just open your browser and access through the following address:

`localhost:8080`

And that's it! You already have an OcoMon installation ready for testing with the following registered users:<br>


| user      | Password | Description                    |
| :-------- | :------- | :----------------------------- |
| admin     | admin    | System administration level    |
| operador  | operador | Standard operator – level 1    |
| abertura  | abertura | Only for opening tickets       |


If you don't have Docker, access the site and install the version for your operating system:

[https://docs.docker.com/get-docker/](https://docs.docker.com/get-docker/)<br>

Or watch this video to see how simple it is to test OcoMon without needing any installation:
[https://www.youtube.com/watch?v=Wtq-Z4M9w5M](https://www.youtube.com/watch?v=Wtq-Z4M9w5M)<br>


## FIRST STEPS


ACCESS

user: admin
    
password: admin (Don't forget to change this password as soon as you have access to the system!!)

New users can be created in the Admin -> Users menu
<br><br>


## GENERAL SYSTEM CONFIGURATIONS


Some configurations need to be adjusted depending on the intended use for the system:

- Configuration file: /includes/config.inc.php
    - this file contains database connection information;


Some services need to be enabled in the system task scheduler (cron in Linux). In the `install/crontab` folder there is the `crontab.txt` file with the example configuration for `crontab`.

- sendEmail.php: service responsible for email queue management
* update_auto_approval.php: service responsible for auto-approval of services;
* update_auto_close_due_inactivity.php: service responsible for auto-closing in configured cases;
* getMailAndOpenTicket.php: service responsible for opening tickets by email
* getMailAndOpenTicketAzure.php: service responsible for opening tickets by email in Azure cases


To enable request quantity control, if using the API directly or through email ticket opening, the Apache user needs write permission to the "api/ocomon_api/storage" directory.


The remaining system configurations are all accessible through the administration menu directly in the system interface.
<br><br>


## INTEGRATION:

Access the documentation at [https://ocomon.com.br/site/integracao/](https://ocomon.com.br/site/integracao/)

## DOCUMENTATION:


All OcoMon documentation is available on the project website and YouTube channel:

+ Official website: [https://ocomon.com.br/site/](https://ocomon.com.br/site/)

+ Changelog: [https://ocomon.com.br/site/changelog-incremental/](https://ocomon.com.br/site/changelog-incremental/)

+ YouTube Channel: [https://www.youtube.com/c/OcoMonOficial](https://www.youtube.com/c/OcoMonOficial)




## Donations

Friends, as you can imagine, developing and maintaining free software for the community is an expensive activity that requires a lot of dedication, motivation, and effort to keep the project relevant and continue adding good features and evolving in various aspects.

Can you imagine the amount of time invested in planning, development, content creation, and free support for the community? I guarantee it's not little.

All this occurs because of belief in the Free Software cause. Believing in free software is also believing that together we are stronger and that this way we can achieve accomplishments and make a difference in this unequal society.

If OcoMon has been useful to you, saved your work, and allowed you to direct your resources to other investments, consider contributing to the project's continuity and growth: [https://ocomon.com.br/site/doacoes/](https://ocomon.com.br/site/doacoes/)

<br>I am convinced that OcoMon has the potential to be the tool that will be indispensable to you in organizing and managing your service area, freeing your precious time for other accomplishments.

Good use!! :)
