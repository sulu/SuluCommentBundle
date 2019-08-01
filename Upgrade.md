# Upgrade

## dev-develop

### Index length of threads type/entityId

In order to allow `utf8mb4` it was neccesary to down size the length of the fields `type` and `entityId` within the
Thread. The following SQL upgrades your database schema. But be sure that the types you are not longer than 64
characters.

```sql
ALTER TABLE com_threads CHANGE type type VARCHAR(64) NOT NULL, CHANGE entityId entityId VARCHAR(64) NOT NULL;
```
