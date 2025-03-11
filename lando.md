# Lando

Start App:

`lando start`

Stop App:

`lando stop`

Destroy App:

`lando destroy`

Stop ALL Docker containers:

`docker stop $(docker ps -a -q)`

Cleanup ALL Docker entities:

`docker system prune`

Import DB:

`lando db-import <path/to/filename>`

Run Next:

`cd ctnext/`

`lando npm run dev`

