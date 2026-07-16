# Sales Flow

This diagram illustrates the order-to-cash process, covering sales orders, fulfillment, invoicing, and payment collection.

```mermaid
flowchart TD
    A[Sales Order] --> B[Invoice]
    B --> C[Payment]
    B --> D[Receivable]
    C --> D
    A --> E[Stock Reduction]
```

## Notes
- Sales Order fulfillment triggers a Stock Reduction in the Inventory module.
- Payments clear outstanding Receivables.
