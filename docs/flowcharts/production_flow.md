# Production Flow

This diagram outlines the process of converting raw materials into finished goods through production orders based on a Bill of Material (BOM).

```mermaid
flowchart TD
    A[Bill of Material] --> B[Production Order]
    B --> C[Material Consumption]
    C --> D[Finished Goods]
    D --> E[Inventory Update]
```

## Notes
- Material Consumption decreases raw material inventory.
- Finished Goods increases product inventory.
