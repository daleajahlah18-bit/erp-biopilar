# Database Relationship Flow

This diagram provides a conceptual Entity-Relationship (ER) flow, illustrating how primary tables link to one another to track data.

```mermaid
flowchart TD
    A[(Products)] --> B[(Purchase Orders)]
    B --> C[(Goods Receipt)]
    C --> D[(Inventory)]
    D --> E[(Production)]
    E --> F[(Project Reports)]
```

## Notes
- This is a conceptual flow and does not represent direct SQL foreign keys 1:1, but rather how data structurally cascades.
- Products act as the central anchor for transactional records (PO, GR, Inventory movements).
