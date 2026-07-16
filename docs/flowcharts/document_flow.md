# Document Flow

This diagram traces the lifecycle and transformation of core business documents throughout the supply chain and financial tracking.

```mermaid
flowchart TD
    A[Purchase Order] --> B[Goods Receipt]
    B --> C[Inventory]
    C --> D[Production]
    D --> E[Material Usage]
    E --> F[HPP]
    F --> G[Project Report]
    G --> H[Management]
```

## Notes
- The Purchase Order is the initiating document for procurement.
- Goods Receipt confirms the physical and financial liability of the PO, updating Inventory.
- Material Usage deducts from Inventory and feeds into HPP (Harga Pokok Penjualan) calculation.
