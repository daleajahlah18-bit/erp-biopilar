# Overall System Flow

This diagram shows the complete ERP workflow, covering the macro-level end-to-end business process from initial project survey down to management reporting.

```mermaid
flowchart TD
    A[Survey Report] --> B[Quotation / Future]
    B --> C[Master Project]
    C --> D[Purchase Order]
    D --> E[Goods Receipt]
    E --> F[Inventory]
    F --> G[Production]
    G --> H[Project Progress]
    H --> I[Daily Report]
    I --> J[Report Phase / BAPP]
    F --> K[Material Usage Summary]
    K --> L[HPP]
    J --> M[Project Report]
    L --> M
    M --> N[Management Report]

    style A fill:#f9f,stroke:#333,stroke-width:2px
    style N fill:#bbf,stroke:#333,stroke-width:2px
```

## Notes
- Quotation is marked as a future expansion.
- Project Progress relies on Production and Inventory data.
- HPP (Harga Pokok Penjualan) and Project Report feed into final Management Reports.
