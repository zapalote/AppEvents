# App Events manager

This project implements a simple app events collection backend and a statistics dashboard (react) 
to visualize user engagement.

The original motivation was to allow for interactive websites to record and analyze anonymous 
usage data **without** using cookies or tracking software.

The current implementation is based on a simple LAMP (Linux/Apache/MySQL/PHP) architecture to make 
it deployable on any small or medium site. It would be easy though to replace the backend with a 
cloud based implementation to make it scale-up.

In order to log events, your website or app needs to submit events through `HTTP GET` requests 
to the backend. This is typically done through ajax javascript functions within your website pages, 
or through a suitable logging function in your app. Said functionality is not part of this package, 
nevertheless an example of such a script is provided below ([see Logging](#logging-events)).


# Backend setup and configuration

## Database

The mysql or mariadb software must be installed on the server before hand. 
The database and its user credentials are created as a first step. 
This is typically done via the mysql admin console (e.g. `phpMyAdmin`).

The db details and access credentials are stored in an `.ini` file in the following format
```
[database] 
servername = localhost 
username = <db_username> 
password = <password> 
dbname = <database_name>
log_table = <table_name_for_event_logging>
```

This file is stored typically in a directory outside the root path of the web server, 
such as to block non-admin access to it. On my hosting service the root path for the site is 
`domains/mydomain.com/public_html/` and the `.ini` files is located under 
`domains/mydomain.com/app-events.ini`  

>**WARNING** This approach to storing db credentials is adequate for logging data but NOT safe for any sensitive data.
DO NOT share one and the same database for logging and other data you may store as part of your 
website functionality. 


## Backend configuration

The backend API is configured via the `config.php` file and is located under the deployment directory 
(`/public` before that). 
``` 
<?php
mb_internal_encoding("UTF-8");
date_default_timezone_set ("Europe/Amsterdam");
setlocale(LC_TIME, 'en_NL');

// App wide constants
define('STATS_DB_DEV_INI', "../private/app-events.ini");
define('STATS_DB_PROD_INI', "../../app-events.ini");
define('STATS_SITE', "mydomain.com");

// Don't change beyond here
define('STATS_LOCK', true);

// Connect to the database
require_once('db.php');
$db = db_connect();
``` 
`STATS_DB_PROD_INI` points to the locaion of the database `.ini` file, 
`STATS_DB_DEV_INI` for the same file in a development environment.

## Logging events

The logging API consist of a single entry point, defined as follows

```
log.php?log=[event name: String]&r=[referral: String, optional]&l=[landing page: String, optional]
```

Referral and landing are optional.  

**Example 1**  — A logging request signaling the homepage has been reached can be created with a 
script snippet like this
```
<script>
  const ref = encodeURIComponent(document.referrer);
  const landing = encodeURIComponent(window.location.href);
  fetch('app-events/log.php?log=homepage&r=${ref}&l=${landing}');
</script>
```
provided the package has been deployed under a subdirectory `app-events/` under the website doc root.
No need to read any response, as the the log API returns nothing.

**Example 2** — Signaling "contact me" button results 
```
<script>
  const contactEvent = () => {
    // contact me 
    if(!confirm("Would you like to open your mail client?")){
          fetch('app-events/log.php?log=contact-nok');
          return false;
    } 
    fetch('app-events/log.php?log=contact-ok');
  }
</script>

...

<a href="mailto:me@mydomain.com?subject=Please contact me" onclick="contactEvent()">Contact me</a>
```

## Required backend software

The minimum versions required for the backend are  
>`MariaDB Server version: 10.3.24-MariaDB-cll-lve`  
>`PHP version: 5.5.38`

# Dashboard app

## Installation

Clone this repo.  
```
% cd <app-events-download-path>
% yarn install
% yarn build
```
Create a subdirectory `app-events` under your public html website document root.  

Upload the contents of the `build/` subdirectory to `app-events`  

Update your html pages or app to start recording events  ([see Logging above](#logging-events)).

Open `https://yourwebsite.com/app-events/` on a web broswer.

Have fun!
