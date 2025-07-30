To start the docker containers simply run

`docker-compose up -d`

To change into the container

`docker exec -it symfony_php bash`

To execute the file system watcher command simply run

`php bin/console app:monitor-directory`

It will watch for changes in `var/monitor_me`