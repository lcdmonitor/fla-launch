# Database migrations

Schema for `fla-launch` is managed with [Liquibase](https://www.liquibase.org/), using a single formatted-SQL changelog: [`changelog-master.sql`](changelog-master.sql). This file is the source of truth for the schema — there is no other `.sql` dump anywhere in the repo.

## Prerequisites

```
brew install liquibase
```

Homebrew's `liquibase` formula does **not** bundle a MySQL driver (it ships Oracle/MSSQL/Snowflake/SQLite drivers, but not MySQL — confirmed by inspecting the install). You need MySQL Connector/J separately:

```
mkdir -p ~/.liquibase/drivers
curl -sL -o ~/.liquibase/drivers/mysql-connector-j.jar \
  "https://repo1.maven.org/maven2/com/mysql/mysql-connector-j/9.1.0/mysql-connector-j-9.1.0.jar"
```

Every command below passes `--classpath` pointing at that jar so Liquibase can find it.

## Running the migration

Uses the same `FLA_DB_*` environment variables the app itself reads (see the root [README.md](../README.md) and [CLAUDE.md](../CLAUDE.md)) — no separate credential setup.

Preview the SQL Liquibase would run, without touching the database:

```
liquibase \
  --classpath="$HOME/.liquibase/drivers/mysql-connector-j.jar" \
  --changelog-file=migrations/changelog-master.sql \
  --url="jdbc:mysql://${FLA_DB_HOST:-localhost}:${FLA_DB_PORT:-8889}/${FLA_DB_NAME:-web}" \
  --username="$FLA_DB_USER" \
  --password="$FLA_DB_PASS" \
  updateSQL
```

Apply it:

```
liquibase \
  --classpath="$HOME/.liquibase/drivers/mysql-connector-j.jar" \
  --changelog-file=migrations/changelog-master.sql \
  --url="jdbc:mysql://${FLA_DB_HOST:-localhost}:${FLA_DB_PORT:-8889}/${FLA_DB_NAME:-web}" \
  --username="$FLA_DB_USER" \
  --password="$FLA_DB_PASS" \
  update
```

Preview the rollback SQL without touching the database:

```
liquibase \
  --classpath="$HOME/.liquibase/drivers/mysql-connector-j.jar" \
  --changelog-file=migrations/changelog-master.sql \
  --url="jdbc:mysql://${FLA_DB_HOST:-localhost}:${FLA_DB_PORT:-8889}/${FLA_DB_NAME:-web}" \
  --username="$FLA_DB_USER" \
  --password="$FLA_DB_PASS" \
  rollback-count-sql --count=14
```

Roll back the whole thing (each changeset carries a `--rollback` statement):

```
liquibase \
  --classpath="$HOME/.liquibase/drivers/mysql-connector-j.jar" \
  --changelog-file=migrations/changelog-master.sql \
  --url="jdbc:mysql://${FLA_DB_HOST:-localhost}:${FLA_DB_PORT:-8889}/${FLA_DB_NAME:-web}" \
  --username="$FLA_DB_USER" \
  --password="$FLA_DB_PASS" \
  rollback-count --count=14
```

## Schema notes

- `Role` seeds two rows (`1 = Admin`, `2 = Member`) — `User.RoleID` has a foreign key to `Role.RoleID`, so a role must exist before a user can reference it.
- `Username` and `Email` are both `UNIQUE` on `User`, since login (`ValidateLogin()` in `includes/functions.inc.php`) accepts either interchangeably and expects each to resolve to at most one account.
- All tables use `utf8mb4`/InnoDB. No app code depends on column *names* changing here — only types/constraints/charset were designed fresh.
- Changeset 5 seeds one admin login: username `admin`, email `support@flalaunch.com`, RoleID 1 (Admin). The `PasswordHash` is a real bcrypt hash (`password_hash(..., PASSWORD_DEFAULT)`, verified to round-trip with `password_verify()`) — the plaintext is whatever was chosen when this migration was written; change it via the app's `ChangePassword()` flow after first login rather than reading it out of this file.
