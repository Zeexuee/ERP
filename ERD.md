erDiagram
    customers {
        bigint id PK
        string name
        string email
        string phone
        text address
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at "Soft Delete"
    }

    products {
        bigint id PK
        string sku "Unique"
        string name
        decimal price
        integer stock_quantity "Read-only for Sales"
        timestamp created_at
        timestamp updated_at
    }

    quotations {
        bigint id PK
        bigint customer_id FK
        string quotation_number "Unique"
        string status "draft, sent, accepted, rejected"
        decimal total_amount
        date valid_until
        timestamp created_at
        timestamp updated_at
    }

    quotation_items {
        bigint id PK
        bigint quotation_id FK
        bigint product_id FK
        integer quantity
        decimal unit_price
        decimal subtotal
    }

    sales_orders {
        bigint id PK
        bigint customer_id FK
        bigint quotation_id FK "Nullable"
        string order_number "Unique"
        string status "draft, confirmed, processing, ready, completed, cancelled"
        decimal total_amount
        timestamp created_at
        timestamp updated_at
    }

    sales_order_items {
        bigint id PK
        bigint sales_order_id FK
        bigint product_id FK
        integer quantity
        decimal unit_price
        decimal subtotal
    }

    production_requests {
        bigint id PK
        bigint sales_order_id FK
        string request_number "Unique"
        string status "pending, in_production, finished"
        timestamp requested_date
        timestamp completed_date "Nullable"
        timestamp created_at
        timestamp updated_at
    }

    invoices {
        bigint id PK
        bigint sales_order_id FK
        string invoice_number "Unique"
        string status "unpaid, partial, paid, cancelled"
        decimal total_amount
        date due_date
        timestamp created_at
        timestamp updated_at
    }

    payments {
        bigint id PK
        bigint invoice_id FK
        string payment_number "Unique"
        decimal amount
        string payment_method
        date payment_date
        timestamp created_at
        timestamp updated_at
    }

    customers ||--o{ quotations : "has"
    customers ||--o{ sales_orders : "places"
    products ||--o{ quotation_items : "included in"
    products ||--o{ sales_order_items : "included in"
    quotations ||--o{ quotation_items : "contains"
    quotations ||--o| sales_orders : "converts to"
    sales_orders ||--o{ sales_order_items : "contains"
    sales_orders ||--o| production_requests : "triggers"
    sales_orders ||--o{ invoices : "billed via"
    invoices ||--o{ payments : "receives"