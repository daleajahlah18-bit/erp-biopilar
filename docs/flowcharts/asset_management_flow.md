# Asset Management Flow

This diagram tracks the lifecycle of company assets from acquisition to depreciation and reporting.

```mermaid
flowchart TD
    A[Asset Registration] --> B[Improvement]
    A --> C[Maintenance]
    A --> D[Movement]
    A --> E[Depreciation]
    B --> E
    C --> E
    D --> F[Asset Report]
    E --> F
```

## Notes
- Improvements and Maintenance can affect the overall valuation and Depreciation calculations of the asset.
- Asset Movement records track the physical location or assignment of the asset.
