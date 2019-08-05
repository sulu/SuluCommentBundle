# Upgrade

## dev-develop

### Type-Hints

We have added type-hints to the whole codebase. Therefor the function parameter and returns validation is stricter
than before.

Additionally we have remove the possibility to pass a single ID to the following functions. If you want to delete a
single entity you have to pass an array with a single id.

* CommentManagerInterface::delete
* CommentManagerInterface::deleteThreads

### Index length of threads type/entityId

In order to allow `utf8mb4` it was neccesary to down size the length of the fields `type` and `entityId` within the
Thread. The following SQL upgrades your database schema. But be sure that the types you are not longer than 64
characters.

```sql
ALTER TABLE com_threads CHANGE type type VARCHAR(64) NOT NULL, CHANGE entityId entityId VARCHAR(64) NOT NULL;
```
