# Authentication Flow

This diagram focuses specifically on the logic flow when a user attempts to log into the ERP.

```mermaid
flowchart TD
    A[Login] --> B[Credential Validation]
    B --> C[User Status Check]
    C --> D[Permission Loading]
    D --> E[Dashboard]
    B -- Invalid --> F[Access Denied]
    C -- Inactive --> F
```

## Notes
- The User Status Check ensures that deactivated or suspended users cannot enter the system, even with correct credentials.
- After successful validation, the system caches or loads Permissions for the current session.
