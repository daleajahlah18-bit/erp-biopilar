# Notification Flow

This diagram demonstrates the lifecycle of a system notification, from the triggering event to the user acknowledging it.

```mermaid
flowchart TD
    A[Business Event] --> B[Notification Created]
    B --> C[(Database)]
    C --> D[Unread Notification]
    D --> E[User Opens]
    E --> F[Read]
```

## Notes
- Business Events (e.g., PO awaiting approval) automatically generate Notifications.
- The UI polls or receives push events to show Unread Notifications.
