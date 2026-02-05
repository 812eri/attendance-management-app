# laravel-docker-template

erDiagram
users ||--o{ attendances : "1人が複数の勤怠を持つ"
users ||--o{ stamp_correction_requests : "1人が複数の申請を行う"
attendances ||--o{ rests : "1日に複数の休憩がある"
attendances ||--o{ stamp_correction_requests : "1日の勤怠に対して申請を行う"
stamp_correction_requests ||--o{ stamp_correction_request_rests : "1つの申請に複数の休憩がある"

    users {
        bigint id PK
        string name
        string email
        timestamp email_verified_at
        string password
        tinyint role_id "1:一般, 2:管理者"
        string remember_token
        timestamp created_at
        timestamp updated_at
    }

    attendances {
        bigint id PK
        bigint user_id FK
        date date
        time start_time
        time end_time
        text remarks
        timestamp created_at
        timestamp updated_at
    }

    rests {
        bigint id PK
        bigint attendance_id FK
        time start_time
        time end_time
        timestamp created_at
        timestamp updated_at
    }

    stamp_correction_requests {
        bigint id PK
        bigint user_id FK
        bigint attendance_id FK
        time new_start_time
        time new_end_time
        text new_remarks
        string status "pending/approved"
        timestamp created_at
        timestamp updated_at
    }

    stamp_correction_request_rests {
        bigint id PK
        bigint stamp_correction_request_id FK
        time new_break_start
        time new_break_end
        timestamp created_at
        timestamp updated_at
    }
