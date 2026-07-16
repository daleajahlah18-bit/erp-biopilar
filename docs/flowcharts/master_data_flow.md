# Master Data Flow

This diagram shows the hierarchy and relationships of Master Data entities within the ERP system.

```mermaid
flowchart TD
    subgraph Master Data
        A[Products]
        B[Engineering Category]
        C[Supplier]
        D[Warehouse]
        E[Project]
        F[Asset]
        G[User]
    end

    A --> B
    C --> A
    E --> D
    E --> F
    G --> E
```

## Notes
- Products are categorized by Engineering Categories.
- This is a conceptual relationship representing how master data is structured and linked.
