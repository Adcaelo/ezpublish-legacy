#!/bin/bash

USER="root"

SQLFILES=""
SQLDUMP=""

SCHEMAFILES=""

USE_MYSQL=""
USE_POSTGRESQL=""

function help
{
	    echo "Usage: $0 [options] DBNAME SQLFILE [SQLFILE]..."
	    echo
	    echo "Options: -h"
	    echo "         --help                     This message"
	    echo "         --sql-data-only            Only dump table data"
	    echo "         --sql-schema-only          Only dump table definitions"
	    echo "         --sql-full                 Dump table definition and data (default)"
	    echo "         --mysql                    Redump using MySQL"
	    echo "         --postgresql               Redump using PostgreSQL"
	    echo "         --schema-sql=FILE          Schema sql file to use before the SQLFILE,"
	    echo "                                    useful for data only redumping"
            echo
            echo "Example:"
            echo "$0 tmp data.sql"
}

# Check parameters
for arg in $*; do
    case $arg in
	--help|-h)
	    help
	    exit 1
	    ;;
	--sql-data-only)
	    SQLDUMP="data"
	    NOCREATEINFOARG="-t"
	    ;;
	--sql-schema-only)
	    SQLDUMP="schema"
	    NODATAARG="-n"
	    NOCREATEINFOARG=""
	    ;;
	--sql-full)
	    SQLDUMP=""
	    NODATAARG=""
	    NOCREATEINFOARG=""
	    ;;
	--mysql)
	    USE_MYSQL="yes"
	    ;;
	--postgresql)
	    USE_POSTGRESQL="yes"
	    ;;
	--schema-sql=*)
	    if echo $arg | grep -e "--schema-sql=" >/dev/null; then
		SCHEMAFILE=`echo $arg | sed 's/--schema-sql=//'`
		SCHEMAFILES="$SCHEMAFILES $SCHEMAFILE"
	    fi
	    ;;
	-*)
	    echo "$arg: unkown option specified"
            $0 -h
	    exit 1
	    ;;
	*)
	    if [ -z $DBNAME ]; then
		DBNAME=$arg
	    elif [ -z $SQLFILE ]; then
		SQLFILE=$arg
	    else
		SQLFILES="$SQLFILES $arg"
	    fi
	    ;;
    esac;
done

if [ -z $DBNAME ]; then
    echo "Missing database name"
    help
    exit 1;
fi
if [ -z $SQLFILE ]; then
    echo "Missing sql file"
    help
    exit 1;
fi
if [ ! -f "$SQLFILE" ]; then
    echo "SQL $SQLFILE file does not exist"
    help
    exit 1;
fi

if [ "$USE_MYSQL" == "" -a "$USE_POSTGRESQL" == "" ]; then
    echo "No database type chosen"
    help
    exit 1
fi

USERARG="-u$USER"

if [ "$USE_MYSQL" != "" ]; then
    mysqladmin "$USERARG" -f drop "$DBNAME"
    mysqladmin "$USERARG" create "$DBNAME"
    for sql in $SCHEMAFILES; do
	echo "Importing schema SQL file $sql"
	mysql "$USERARG" "$DBNAME" < "$sql"
    done
    echo "Importing SQL file $SQLFILE"
    mysql "$USERARG" "$DBNAME" < "$SQLFILE"
    for sql in $SQLFILES; do
	echo "Importing SQL file $sql"
	mysql "$USERARG" "$DBNAME" < "$sql"
    done
    ./update/common/scripts/flatten.php --db-driver=ezmysql --db-database=$DBNAME --db-user=$USER all
    ./update/common/scripts/cleanup.php --db-driver=ezmysql --db-database=$DBNAME --db-user=$USER all

    echo "Dumping to SQL file $SQLFILE"
# mysqldump "$USERARG" -c --quick "$NODATAARG" "$NOCREATEINFOARG" -B"$DBNAME" > "$SQLFILE".0
    if [ "$SQLDUMP" == "schema" ]; then
	mysqldump "$USERARG" -c --quick -d "$DBNAME" | perl -pi -e "s/(^--.*$)|(^#.*$)//g" > "$SQLFILE".0
    elif [ "$SQLDUMP" == "data" ]; then
	mysqldump "$USERARG" -c --quick -t "$DBNAME" | perl -pi -e "s/(^--.*$)|(^#.*$)//g" > "$SQLFILE".0
    else
	mysqldump "$USERARG" -c --quick "$DBNAME" | perl -pi -e "s/(^--.*$)|(^#.*$)//g" > "$SQLFILE".0
    fi
    perl -pi -e "s/(^--.*$)|(^#.*$)//g" "$SQLFILE".0
else
    dropdb "$DBNAME"
    createdb "$DBNAME"
    for sql in $SCHEMAFILES; do
	echo "Importing schema SQL file $sql"
	psql "$DBNAME" < "$sql" &>/dev/null
    done
    echo "Importing SQL file $SQLFILE"
    psql "$DBNAME" < "$SQLFILE" &>/dev/null
    for sql in $SQLFILES; do
	echo "Importing SQL file $sql"
	psql "$DBNAME" < "$sql" &>/dev/null
    done
    echo "Dumping to SQL file $SQLFILE"
# mysqldump "$USERARG" -c --quick "$NODATAARG" "$NOCREATEINFOARG" -B"$DBNAME" > "$SQLFILE".0
    if [ "$SQLDUMP" == "schema" ]; then
	pg_dump --no-owner --inserts --schema-only "$DBNAME" > "$SQLFILE".0
    elif [ "$SQLDUMP" == "data" ]; then
	pg_dump --no-owner --inserts --data-only "$DBNAME" > "$SQLFILE".0
    else
	pg_dump --no-owner --inserts "$DBNAME" > "$SQLFILE".0
    fi
    perl -pi -e "s/(^--.*$)|(^#.*$)//g" "$SQLFILE".0
fi

if [ $? -eq 0 ]; then
    mv "$SQLFILE" "$SQLFILE"~
    mv "$SQLFILE".0 "$SQLFILE"
    echo "Redumped $SQLFILE using $DBNAME database"
else
    rm "$SQLFILE".0
    echo "Failed dumping database $DBNAME to $SQLFILE"
    exit 1
fi

