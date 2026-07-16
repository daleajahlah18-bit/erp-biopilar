# Project Report Flow

This diagram captures the ongoing tracking of project execution, linking field reports to material usage and final cost summaries (HPP).

```mermaid
flowchart TD
    A[Master Project] --> B[Project Progress]
    B --> C[Daily Report]
    C --> D[Report Phase]
    D --> E[Material Usage Summary]
    E --> F[HPP Summary]
    F --> G[Project Report]
```

## Notes
- Report Phase (BAPP) represents milestone completions based on the compiled Daily Reports.
- HPP (Harga Pokok Penjualan) Summary aggregates the categorized material costs for financial overview.
