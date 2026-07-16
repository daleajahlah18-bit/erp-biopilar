# User Management Flow

This diagram illustrates how users are provisioned, authenticated, and authorized to perform actions within the ERP system.

```mermaid
flowchart TD
    A[Login] --> B[Authentication]
    B --> C[Role]
    C --> D[Permission Matrix]
    D --> E[Sidebar Visibility]
    E --> F[CRUD Authorization]
    F --> G[Activity Log]
```

## Notes
- Roles group multiple Permissions together.
- Sidebar Visibility and CRUD (Create, Read, Update, Delete) limits are dictated by the assigned Role's Permission Matrix.
- Any authorized CRUD action creates an entry in the Activity Log for audit trailing.
