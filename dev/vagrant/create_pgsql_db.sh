#!/bin/bash

createuser test_user --no-createdb --no-createrole --no-superuser
psql -d template1 -c "ALTER USER test_user WITH ENCRYPTED PASSWORD 'jelix'"
createdb -E UTF8 -O test_user testapp
psql -d testapp -c "GRANT ALL PRIVILEGES ON DATABASE testapp TO test_user;"
psql -d testapp -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO test_user;"
psql -d testapp -c "GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO test_user;"
psql -d testapp -c "GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO test_user;"
