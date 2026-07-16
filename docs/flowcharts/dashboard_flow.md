# Dashboard Flow

This diagram illustrates the user login and dashboard interaction flow, including authentication, permission checking, and subsequent dashboard widgets.

```mermaid
flowchart TD
    A[Login] --> B{Authentication}
    B -- Success --> C[Permission]
    B -- Failed --> A
    C --> D[Dashboard]
    D --> E[Widgets]
    D --> F[Activity Logs]
    D --> G[Notifications]
```

## Notes
- Users must successfully authenticate to reach the permission check.
- Widgets displayed are contextual based on the user's role and permissions.
