-- MarketLabs Database Schema
-- Generated: 2026-09-10 05:54:13

CREATE TABLE "activity_logs" ("id" varchar not null, "user_id" varchar, "user_name" varchar, "role" varchar, "action" varchar not null, "description" text, "subject_type" varchar, "subject_id" varchar, "properties" text, "ip_address" varchar, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete set null, primary key ("id"));

CREATE TABLE "bench_fee_rates" ("id" varchar not null, "level" varchar not null, "type" varchar not null, "category" varchar not null default 'biomedis', "rate" integer not null, "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "borrowing_items" ("id" varchar not null, "borrowing_id" varchar not null, "tool_id" varchar not null, "quantity" integer not null default '1', "price_per_day" integer not null default '0', "created_at" datetime, "updated_at" datetime, foreign key("borrowing_id") references "borrowings"("id") on delete cascade, foreign key("tool_id") references "tools"("id") on delete cascade, primary key ("id"));

CREATE TABLE "borrowings" ("id" varchar not null, "code" varchar not null, "invoice_number" varchar, "user_id" varchar not null, "status" varchar check ("status" in ('pending', 'approved', 'rejected', 'borrowed', 'returned', 'cancelled')) not null default 'pending', "borrower_type" varchar check ("borrower_type" in ('internal', 'eksternal')) not null default 'internal', "nim_nip" varchar, "institution" varchar, "purpose" text, "borrow_date" date not null, "return_date" date not null, "discount" integer not null default '0', "penalty" integer not null default '0', "is_free" tinyint(1) not null default '0', "pickup_notes" text, "rejection_reason" text, "notes" text, "document_path" varchar, "payment_status" varchar check ("payment_status" in ('unpaid', 'paid')) not null default 'unpaid', "processed_at" datetime, "returned_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade, primary key ("id"));

CREATE TABLE "cache" ("key" varchar not null, "value" text not null, "expiration" integer not null, primary key ("key"));

CREATE TABLE "cache_locks" ("key" varchar not null, "owner" varchar not null, "expiration" integer not null, primary key ("key"));

CREATE TABLE "contacts" ("id" varchar not null, "name" varchar not null, "email" varchar not null, "message" text not null, "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "event_registrations" ("id" varchar not null, "event_id" varchar not null, "user_id" varchar not null, "registered_by" varchar, "status" varchar not null default 'registered', "answers" text, "attendance_token" varchar, "attendance_answers" text, "attended_at" datetime, "certificate_number" varchar, "certificate_path" varchar, "certificate_back_path" varchar, "certificate_generated_at" datetime, "certificate_status" varchar, "certificate_error" text, "created_at" datetime, "updated_at" datetime, foreign key("event_id") references "events"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade, foreign key("registered_by") references "users"("id") on delete set null, primary key ("id"));

CREATE TABLE "events" ("id" varchar not null, "code" varchar not null, "slug" varchar not null, "title" varchar not null, "description" text, "location" varchar, "starts_at" datetime, "ends_at" datetime, "quota" integer, "fee" numeric, "discount" numeric, "registration_deadline" datetime, "status" varchar not null default 'draft', "mode" varchar, "image" varchar, "poster" varchar, "form_fields" text, "attendance_fields" text, "certificate_template" varchar, "certificate_template_back" varchar, "certificate_font" varchar, "certificate_layout" text, "certificate_layout_back" text, "certificate_batch_status" varchar, "certificate_batch_total" integer not null default '0', "certificate_batch_done" integer not null default '0', "attendance_enabled" tinyint(1) not null default '1', "created_by" varchar, "created_at" datetime, "updated_at" datetime, foreign key("created_by") references "users"("id") on delete set null, primary key ("id"));

CREATE TABLE "examiner_weekly_schedules" ("id" varchar not null, "user_id" varchar not null, "month" varchar not null, "day_of_week" integer not null, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade, primary key ("id"));

CREATE TABLE "failed_jobs" ("id" integer primary key autoincrement not null, "uuid" varchar not null, "connection" text not null, "queue" text not null, "payload" text not null, "exception" text not null, "failed_at" datetime not null default CURRENT_TIMESTAMP);

CREATE TABLE "faqs" ("id" varchar not null, "question" varchar not null, "answer" text not null, "sort_order" integer not null default '0', "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "footer_logos" ("id" varchar not null, "name" varchar not null, "image" varchar not null, "url" varchar, "sort_order" integer not null default '0', "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "gallery_images" ("id" varchar not null, "title" varchar, "caption" varchar, "path" varchar not null, "sort_order" integer not null default '0', "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "health_checkups" ("id" varchar not null, "user_id" varchar not null, "type_id" varchar not null, "examiner_id" varchar, "code" varchar not null, "booking_date" date not null, "queue_number" integer not null, "purpose" varchar, "status" varchar not null default 'pending', "result" varchar, "result_notes" text, "result_file" varchar, "payment_status" varchar not null default 'unpaid', "invoice_number" varchar, "processed_at" datetime, "approved_at" datetime, "done_at" datetime, "rejected_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade, foreign key("type_id") references "health_test_types"("id") on delete restrict, foreign key("examiner_id") references "users"("id") on delete set null, primary key ("id"));

CREATE TABLE "health_test_types" ("id" varchar not null, "key" varchar not null, "name" varchar not null, "description" text, "price" integer not null, "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "job_batches" ("id" varchar not null, "name" varchar not null, "total_jobs" integer not null, "pending_jobs" integer not null, "failed_jobs" integer not null, "failed_job_ids" text not null, "options" text, "cancelled_at" integer, "created_at" integer not null, "finished_at" integer, primary key ("id"));

CREATE TABLE "jobs" ("id" integer primary key autoincrement not null, "queue" varchar not null, "payload" text not null, "attempts" integer not null, "reserved_at" integer, "available_at" integer not null, "created_at" integer not null);

CREATE TABLE "laboratoriums" ("id" varchar not null, "name" varchar not null, "code" varchar, "description" text, "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "landing_page_sections" ("id" integer primary key autoincrement not null, "key" varchar not null, "title" varchar not null, "description" text, "sort_order" integer not null default '0', "is_active" tinyint(1) not null default '1', "content" text, "created_at" datetime, "updated_at" datetime);

CREATE TABLE "menu_items" ("id" integer primary key autoincrement not null, "group" varchar not null, "label" varchar not null, "route_name" varchar, "url" varchar, "icon" varchar, "sort_order" integer not null default '0', "is_active" tinyint(1) not null default '1', "min_role" varchar, "created_at" datetime, "updated_at" datetime);

CREATE TABLE "mitras" ("id" integer primary key autoincrement not null, "name" varchar not null, "logo" varchar, "website" varchar, "is_active" tinyint(1) not null default '1', "sort_order" integer not null default '0', "created_at" datetime, "updated_at" datetime);

CREATE TABLE "notifications" ("id" varchar not null, "type" varchar not null, "notifiable_type" varchar not null, "notifiable_id" varchar not null, "data" text not null, "read_at" datetime, "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "password_reset_tokens" ("email" varchar not null, "token" varchar not null, "created_at" datetime, primary key ("email"));

CREATE TABLE "research_logbooks" ("id" varchar not null, "research_proposal_id" varchar not null, "log_date" date not null, "note" text not null, "obstacle" text, "created_at" datetime, "updated_at" datetime, foreign key("research_proposal_id") references "research_proposals"("id") on delete cascade, primary key ("id"));

CREATE TABLE "research_proposal_members" ("id" varchar not null, "research_proposal_id" varchar not null, "name" varchar not null, "role" varchar, "user_id" varchar, "created_at" datetime, "updated_at" datetime, foreign key("research_proposal_id") references "research_proposals"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete set null, primary key ("id"));

CREATE TABLE "research_proposal_tools" ("research_proposal_id" varchar not null, "tool_id" varchar not null, "quantity" integer not null default '1', "days" integer not null default '1', "created_at" datetime, "updated_at" datetime, foreign key("research_proposal_id") references "research_proposals"("id") on delete cascade, foreign key("tool_id") references "tools"("id") on delete cascade, primary key ("research_proposal_id", "tool_id"));

CREATE TABLE "research_proposals" ("id" varchar not null, "user_id" varchar not null, "code" varchar not null, "title" varchar not null, "field" varchar, "description" text, "objectives" text, "institution" varchar, "customer_type" varchar, "nim_nip" varchar, "start_date" date, "end_date" date, "status" varchar not null default 'pending', "admin_notes" text, "document_path" varchar, "letter_path" varchar, "replacement_letter_path" varchar, "bench_fee" integer, "bench_fee_level" varchar, "bench_fee_type" varchar, "bench_fee_category" varchar, "needs_laboran" tinyint(1) not null default '0', "laboratorium_id" varchar, "laboran_id" varchar, "laboran_fee" integer, "penalty" integer not null default '0', "penalty_note" varchar, "payment_status" varchar not null default 'unpaid', "invoice_number" varchar, "processed_at" datetime, "approved_at" datetime, "ongoing_at" datetime, "done_at" datetime, "rejected_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade, foreign key("laboratorium_id") references "laboratoriums"("id") on delete set null, foreign key("laboran_id") references "users"("id") on delete set null, primary key ("id"));

CREATE TABLE "sample_forms" ("id" varchar not null, "name" varchar not null, "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "sample_test_items" ("id" varchar not null, "sample_test_id" varchar not null, "parameter_id" varchar, "sample_form_id" varchar, "sample_type_id" varchar, "sample_name" varchar not null, "sample_description" text, "quantity" integer not null default '1', "rate" integer not null default '0', "created_at" datetime, "updated_at" datetime, foreign key("sample_test_id") references "sample_tests"("id") on delete cascade, foreign key("parameter_id") references "test_parameters"("id") on delete set null, foreign key("sample_form_id") references "sample_forms"("id") on delete set null, foreign key("sample_type_id") references "sample_types"("id") on delete set null, primary key ("id"));

CREATE TABLE "sample_tests" ("id" varchar not null, "user_id" varchar not null, "laboran_id" varchar, "code" varchar not null, "notes" text, "delivery_method" varchar, "status" varchar not null default 'pending', "result" varchar, "result_notes" varchar, "result_file" varchar, "total_cost" integer not null default '0', "payment_status" varchar not null default 'unpaid', "invoice_number" varchar, "processed_at" datetime, "approved_at" datetime, "received_at" datetime, "tested_at" datetime, "done_at" datetime, "rejected_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade, foreign key("laboran_id") references "users"("id") on delete set null, primary key ("id"));

CREATE TABLE "sample_types" ("id" varchar not null, "name" varchar not null, "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "sample_units" ("id" varchar not null, "name" varchar not null, "symbol" varchar, "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "sessions" ("id" varchar not null, "user_id" varchar, "ip_address" varchar, "user_agent" text, "payload" text not null, "last_activity" integer not null, primary key ("id"));

CREATE TABLE "settings" ("id" varchar not null, "key" varchar not null, "value" text, "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE sqlite_sequence(name,seq);

CREATE TABLE "test_parameters" ("id" varchar not null, "name" varchar not null, "method" varchar, "unit_id" varchar not null, "rate" integer not null default '0', "description" text, "image" varchar, "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, foreign key("unit_id") references "sample_units"("id") on delete cascade, primary key ("id"));

CREATE TABLE "testimonials" ("id" varchar not null, "name" varchar not null, "role" varchar, "quote" text not null, "rating" integer not null default '5', "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "tool_categories" ("id" varchar not null, "name" varchar not null, "created_at" datetime, "updated_at" datetime, primary key ("id"));

CREATE TABLE "tool_images" ("id" varchar not null, "tool_id" varchar not null, "path" varchar not null, "sort_order" integer not null default '0', "created_at" datetime, "updated_at" datetime, foreign key("tool_id") references "tools"("id") on delete cascade, primary key ("id"));

CREATE TABLE "tools" ("id" varchar not null, "code" varchar not null, "name" varchar not null, "type" varchar not null default 'kesehatan', "category_id" varchar, "brand" varchar, "series" varchar, "description" text, "total_stock" integer not null default '0', "available_stock" integer not null default '0', "price_per_day" integer not null default '0', "image" varchar, "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, foreign key("category_id") references "tool_categories"("id") on delete set null, primary key ("id"));

CREATE TABLE "users" ("id" varchar not null, "name" varchar not null, "username" varchar, "avatar" varchar, "email" varchar, "nim_nip" varchar, "institution" varchar, "participant_code" varchar, "phone" varchar, "role" varchar not null default 'user', "email_verified_at" datetime, "password" varchar not null, "remember_token" varchar, "created_at" datetime, "updated_at" datetime, primary key ("id"));

