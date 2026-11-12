#!/bin/sh
# Crée une base PostgreSQL par microservice (exécuté au premier démarrage du conteneur).
set -e
for db in auth_db patrimoine_db inventaire_db gateway_db; do
  psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname postgres <<-SQL
    CREATE DATABASE $db;
SQL
done
