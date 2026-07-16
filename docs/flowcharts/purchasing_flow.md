# Purchasing Flow

This diagram maps out the procurement process from issuing a Purchase Order to recording the payment and updating inventory.

```mermaid
flowchart TD
    A[Purchase Order] --> B[Approval / if implemented]
    B --> C[Goods Receipt]
    C --> D[Purchase Invoice]
    D --> E[Purchase Payment]
    C --> F[Inventory Update]
    E --> G[Activity Log]
    F --> G
```

## Notes
- Goods Receipt triggers an Inventory Update concurrently with the financial tracking (Invoice & Payment).
- Actions performed are recorded in the Activity Log for audit trails.
