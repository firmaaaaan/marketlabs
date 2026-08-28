# ERD - MarketLabs Database

## Entity Relationship Diagram

```mermaid
erDiagram
    users {
        uuid id PK
        string name
        string email UK
        string nim_nip
        string institution
        string participant_code UK
        string phone
        string role
        timestamp email_verified_at
        string password
        timestamps
    }

    tool_categories {
        uuid id PK
        string name UK
        timestamps
    }

    tools {
        uuid id PK
        string code UK
        string name
        uuid category_id FK
        string brand
        string series
        text description
        int total_stock
        int available_stock
        bigint price_per_day
        string image
        boolean is_active
        timestamps
    }

    sample_units {
        uuid id PK
        string name UK
        string symbol
        boolean is_active
        timestamps
    }

    test_parameters {
        uuid id PK
        string name
        string method
        uuid unit_id FK
        int rate
        text description
        string image
        boolean is_active
        timestamps
    }

    sample_forms {
        uuid id PK
        string name
        boolean is_active
        timestamps
    }

    sample_types {
        uuid id PK
        string name
        boolean is_active
        timestamps
    }

    sample_tests {
        uuid id PK
        uuid user_id FK
        uuid laboran_id FK
        string code UK
        text notes
        string delivery_method
        string status
        string result
        string result_notes
        string result_file
        int total_cost
        string payment_status
        string invoice_number
        timestamp processed_at
        timestamp approved_at
        timestamp received_at
        timestamp tested_at
        timestamp done_at
        timestamp rejected_at
        timestamps
    }

    sample_test_items {
        uuid id PK
        uuid sample_test_id FK
        uuid parameter_id FK
        uuid sample_form_id FK
        uuid sample_type_id FK
        string sample_name
        text sample_description
        int quantity
        int rate
        timestamps
    }

    borrowings {
        uuid id PK
        string code UK
        string invoice_number UK
        uuid user_id FK
        enum status
        enum borrower_type
        string nim_nip
        string institution
        text purpose
        date borrow_date
        date return_date
        int discount
        int penalty
        text pickup_notes
        text rejection_reason
        text notes
        string document_path
        enum payment_status
        timestamp processed_at
        timestamp returned_at
        timestamps
    }

    borrowing_items {
        uuid id PK
        uuid borrowing_id FK
        uuid tool_id FK
        int quantity
        bigint price_per_day
        timestamps
    }

    laboratoriums {
        uuid id PK
        string name
        string code UK
        text description
        boolean is_active
        timestamps
    }

    bench_fee_rates {
        uuid id PK
        string level
        string type
        string category
        int rate
        timestamps
    }

    research_proposals {
        uuid id PK
        uuid user_id FK
        string code UK
        string title
        string field
        text description
        text objectives
        string institution
        string customer_type
        string nim_nip
        date start_date
        date end_date
        string status
        text admin_notes
        string document_path
        string letter_path
        string replacement_letter_path
        int bench_fee
        string bench_fee_level
        string bench_fee_type
        string bench_fee_category
        boolean needs_laboran
        uuid laboratorium_id FK
        uuid laboran_id FK
        bigint laboran_fee
        int penalty
        string penalty_note
        string payment_status
        string invoice_number UK
        timestamp processed_at
        timestamp approved_at
        timestamp ongoing_at
        timestamp done_at
        timestamp rejected_at
        timestamps
    }

    research_proposal_members {
        uuid id PK
        uuid research_proposal_id FK
        string name
        string role
        uuid user_id FK
        timestamps
    }

    research_proposal_tools {
        uuid research_proposal_id FK
        uuid tool_id FK
        int quantity
        smallint days
        timestamps
    }

    research_logbooks {
        uuid id PK
        uuid research_proposal_id FK
        date log_date
        text note
        text obstacle
        timestamps
    }

    health_test_types {
        uuid id PK
        string key UK
        string name
        text description
        int price
        boolean is_active
        timestamps
    }

    health_checkups {
        uuid id PK
        uuid user_id FK
        uuid type_id FK
        uuid examiner_id FK
        string code UK
        date booking_date
        int queue_number
        string purpose
        string status
        string result
        text result_notes
        string result_file
        string payment_status
        string invoice_number UK
        timestamp processed_at
        timestamp approved_at
        timestamp done_at
        timestamp rejected_at
        timestamps
    }

    examiner_weekly_schedules {
        uuid id PK
        uuid user_id FK
        string month
        tinyint day_of_week
        timestamps
    }

    events {
        uuid id PK
        string code UK
        string slug UK
        string title
        text description
        string location
        timestamp starts_at
        timestamp ends_at
        int quota
        decimal fee
        decimal discount
        timestamp registration_deadline
        string status
        string mode
        string image
        string poster
        json form_fields
        json attendance_fields
        string certificate_template
        string certificate_template_back
        string certificate_font
        json certificate_layout
        json certificate_layout_back
        boolean attendance_enabled
        uuid created_by FK
        timestamps
    }

    event_registrations {
        uuid id PK
        uuid event_id FK
        uuid user_id FK
        uuid registered_by FK
        string status
        json answers
        string attendance_token UK
        json attendance_answers
        timestamp attended_at
        string certificate_number UK
        string certificate_path
        string certificate_back_path
        timestamp certificate_generated_at
        timestamps
    }

    activity_logs {
        uuid id PK
        uuid user_id FK
        string user_name
        string role
        string action
        text description
        string subject_type
        string subject_id
        json properties
        string ip_address
        timestamps
    }

    contacts {
        uuid id PK
        string name
        string email
        text message
        timestamps
    }

    settings {
        uuid id PK
        string key UK
        text value
        timestamps
    }

    testimonials {
        uuid id PK
        string name
        string role
        text quote
        tinyint rating
        boolean is_active
        timestamps
    }

    faqs {
        uuid id PK
        string question
        text answer
        int sort_order
        boolean is_active
        timestamps
    }

    footer_logos {
        uuid id PK
        string name
        string image
        string url
        int sort_order
        boolean is_active
        timestamps
    }

    menu_items {
        bigint id PK
        string group
        string label
        string route_name
        string url
        string icon
        int sort_order
        boolean is_active
        string min_role
        timestamps
    }

    landing_page_sections {
        bigint id PK
        string key UK
        string title
        text description
        int sort_order
        boolean is_active
        json content
        timestamps
    }

    %% ===== RELATIONSHIPS =====

    %% Tools & Categories
    tool_categories ||--o{ tools : "has"
    tools ||--o{ borrowing_items : "used_in"
    tools ||--o{ research_proposal_tools : "used_in"

    %% Test Parameters & Sample Units
    sample_units ||--o{ test_parameters : "has"
    test_parameters ||--o{ sample_test_items : "used_in"

    %% Sample Forms & Types
    sample_forms ||--o{ sample_test_items : "used_in"
    sample_types ||--o{ sample_test_items : "used_in"

    %% Sample Tests
    users ||--o{ sample_tests : "submits"
    users ||--o{ sample_tests : "processes_as_laboran"
    sample_tests ||--o{ sample_test_items : "has"

    %% Borrowings
    users ||--o{ borrowings : "borrows"
    borrowings ||--o{ borrowing_items : "has"

    %% Research Proposals
    users ||--o{ research_proposals : "submits"
    users ||--o{ research_proposals : "handles_as_laboran"
    laboratoriums ||--o{ research_proposals : "hosts"
    research_proposals ||--o{ research_proposal_members : "has"
    research_proposals ||--o{ research_logbooks : "has"
    users ||--o{ research_proposal_members : "participates_in"

    %% Health Checkups
    health_test_types ||--o{ health_checkups : "has"
    users ||--o{ health_checkups : "books"
    users ||--o{ health_checkups : "examines"
    users ||--o{ examiner_weekly_schedules : "has_schedule"

    %% Events
    users ||--o{ events : "creates"
    events ||--o{ event_registrations : "has"
    users ||--o{ event_registrations : "registers"
    users ||--o{ event_registrations : "registers_for_others"

    %% Activity Logs
    users ||--o{ activity_logs : "generates"
```

## Table Summary

| Table | Purpose |
|-------|---------|
| `users` | Pengguna sistem (superadmin, admin, laboran, user) |
| `tool_categories` | Kategori alat laboratorium |
| `tools` | Alat laboratorium yang tersedia untuk disewa |
| `sample_units` | Satuan pengukuran sampel |
| `test_parameters` | Parameter pengujian sampel |
| `sample_forms` | Bentuk fisik sampel |
| `sample_types` | Jenis sampel |
| `sample_tests` | Pengajuan pengujian sampel |
| `sample_test_items` | Detail item pengujian sampel |
| `borrowings` | Pengajuan peminjaman alat |
| `borrowing_items` | Detail alat yang dipinjam |
| `laboratoriums` | Data laboratorium |
| `bench_fee_rates` | Tarif bench fee riset |
| `research_proposals` | Pengajuan riset/penelitian |
| `research_proposal_members` | Anggota tim riset |
| `research_proposal_tools` | Alat yang digunakan dalam riset |
| `research_logbooks` | Logbook harian penelitian |
| `health_test_types` | Jenis pemeriksaan kesehatan |
| `health_checkups` | Booking pemeriksaan kesehatan |
| `examiner_weekly_schedules` | Jadwal mingguan pemeriksa |
| `events` | Event/workshop yang diadakan |
| `event_registrations` | Pendaftaran event |
| `activity_logs` | Log aktivitas pengguna |
| `contacts` | Pesan dari formulir kontak |
| `settings` | Pengaturan aplikasi (key-value) |
| `testimonials` | Testimoni pengguna |
| `faqs` | Pertanyaan yang sering ditanyakan |
| `footer_logos` | Logo partner di footer |
| `menu_items` | Menu sidebar/topbar |
| `landing_page_sections` | Section halaman landing |

## Key Relationships

### 1. **User & Services**
- User → SampleTest: 1 user bisa submit banyak pengujian sampel
- User → Borrowing: 1 user bisa pinjam banyak alat
- User → ResearchProposal: 1 user bisa ajukan banyak riset
- User → HealthCheckup: 1 user bisa booking banyak pemeriksaan
- User → EventRegistration: 1 user bisa daftar banyak event

### 2. **Sample Testing Flow**
```
SampleTest (1) ──→ (N) SampleTestItem
    ├── parameter_id → TestParameter → SampleUnit
    ├── sample_form_id → SampleForm
    └── sample_type_id → SampleType
```

### 3. **Tool Borrowing Flow**
```
Borrowing (1) ──→ (N) BorrowingItem
    └── tool_id → Tool → ToolCategory
```

### 4. **Research Proposal Flow**
```
ResearchProposal (1) ──→ (N) ResearchProposalMember
ResearchProposal (1) ──→ (N) ResearchLogbook
ResearchProposal (N) ←→ (N) Tool [via research_proposal_tools]
    └── laboratorium_id → Laboratorium
    └── laboran_id → User (role: laboran)
```

### 5. **Health Checkup Flow**
```
HealthCheckup
    ├── user_id → User (pasien)
    ├── type_id → HealthTestType
    └── examiner_id → User (pemeriksa)
```

### 6. **Event Flow**
```
Event (1) ──→ (N) EventRegistration
    ├── created_by → User (admin)
    ├── user_id → User (peserta)
    └── registered_by → User (proxy pendaftar)
```
