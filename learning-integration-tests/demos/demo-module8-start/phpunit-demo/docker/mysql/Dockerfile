FROM mariadb:latest

ADD 01-create.sql /docker-entrypoint-initdb.d/
ADD 02-seed.sql /docker-entrypoint-initdb.d/

COPY mariadb.cnf /etc/mysql/conf.d/
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh
