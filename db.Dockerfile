FROM mysql:5.7

COPY ./nostalrius.sql /docker-entrypoint-initdb.d/
