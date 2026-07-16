# Inventory Flow

This diagram shows how inventory items move through the system, from receipt to stock management and adjustments.

```mermaid
flowchart TD
    A[Goods Receipt] --> B[Stock]
    B --> C[Transfer]
    B --> D[Adjustment]
    B --> E[Stock Opname]
    C --> F[(Item Journal)]
    D --> F
    E --> F
    A --> F
```

## Notes
- All physical and system stock movements (Receipt, Transfer, Adjustment, Opname) are recorded historically in the Item Journal.
