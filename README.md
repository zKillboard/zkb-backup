# zkb-backup
Creates a sqlite backup of killmails known by zkillboard.

# Requirements

Requires curl and sqlite3 php extensions.

# Install

Clone this repository and chdir into it.

Install composer if you don't already have it. Instructions can be found at https://getcomposer.org/download/

Execute:

    ./composer.phar update
    
chdir into the cron directory and execute go.php:

    php go.php

If all is well, you'll see output including the fetcher grabbing individual killmails, the redisq listener, as well as the daily fetcher pulling the killmail_id and hashes. The data is stored under `/data/` of the installed directory.

## Cron

Add this entry to your cronjob, replacing the `~` with the appropriate location:

    ~/zkb-backup/cron/cron.sh
  
That's it. You're done. It will take time to fetch all killmails, be patient.
