-- ================================================
-- SEED DATA FOR SUPABASE
-- Run this AFTER supabase_clean.sql
-- ================================================

-- Insert test users (passwords are hashed for 'password123')
INSERT INTO users (id, name, email, password, created_at, updated_at) VALUES
(1, 'John Doe', 'john@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lsIj3F.N5u4m', NOW(), NOW()),
(2, 'Jane Smith', 'jane@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lsIj3F.N5u4m', NOW(), NOW());

-- Reset sequence for users
SELECT setval('users_id_seq', 2, true);

-- Insert locations for John Doe (Jakarta) with PostGIS Point geometry
INSERT INTO locations (id, user_id, name, address, coordinates, geofence_radius, created_at, updated_at) VALUES
(1, 1, 'Home', 'Menteng, Jakarta Pusat', ST_SetSRID(ST_MakePoint(106.8317, -6.1976), 4326), 100, NOW(), NOW()),
(2, 1, 'Office', 'Sudirman, Jakarta Selatan', ST_SetSRID(ST_MakePoint(106.8092, -6.2088), 4326), 150, NOW(), NOW()),
(3, 1, 'Gym', 'Senayan, Jakarta Selatan', ST_SetSRID(ST_MakePoint(106.7983, -6.2250), 4326), 100, NOW(), NOW()),
(4, 1, 'Supermarket', 'Grand Indonesia, Jakarta', ST_SetSRID(ST_MakePoint(106.8208, -6.1953), 4326), 200, NOW(), NOW()),
(5, 1, 'Parents House', 'Kelapa Gading, Jakarta Utara', ST_SetSRID(ST_MakePoint(106.9056, -6.1576), 4326), 150, NOW(), NOW());

-- Insert locations for Jane Smith (Bandung) with PostGIS Point geometry
INSERT INTO locations (id, user_id, name, address, coordinates, geofence_radius, created_at, updated_at) VALUES
(6, 2, 'Home', 'Dago, Bandung', ST_SetSRID(ST_MakePoint(107.6171, -6.8707), 4326), 100, NOW(), NOW()),
(7, 2, 'Campus', 'ITB Ganesha, Bandung', ST_SetSRID(ST_MakePoint(107.6098, -6.8915), 4326), 200, NOW(), NOW()),
(8, 2, 'Coffee Shop', 'Braga, Bandung', ST_SetSRID(ST_MakePoint(107.6089, -6.9175), 4326), 50, NOW(), NOW()),
(9, 2, 'Library', 'Sudirman, Bandung', ST_SetSRID(ST_MakePoint(107.6186, -6.9147), 4326), 100, NOW(), NOW());

-- Reset sequence for locations
SELECT setval('locations_id_seq', 9, true);

-- Insert reminders for John Doe
INSERT INTO reminders (id, user_id, location_id, title, description, is_active, trigger_on_enter, trigger_on_exit, created_at, updated_at) VALUES
(1, 1, 1, 'Water the plants', 'Remember to water the plants in the living room', true, true, false, NOW(), NOW()),
(2, 1, 1, 'Turn off AC', 'Make sure to turn off the AC before leaving', true, false, true, NOW(), NOW()),
(3, 1, 2, 'Check emails', 'Check and respond to important emails', true, true, false, NOW(), NOW()),
(4, 1, 2, 'Attend standup meeting', 'Daily standup at 10 AM', true, true, false, NOW(), NOW()),
(5, 1, 3, 'Leg day workout', 'Focus on leg exercises today', true, true, false, NOW(), NOW()),
(6, 1, 4, 'Buy groceries', 'Milk, eggs, bread, vegetables', true, true, false, NOW(), NOW()),
(7, 1, 4, 'Get cleaning supplies', 'Detergent, soap, tissues', false, true, false, NOW(), NOW()),
(8, 1, 5, 'Call mom', 'Weekly call with mom', true, true, false, NOW(), NOW());

-- Insert reminders for Jane Smith
INSERT INTO reminders (id, user_id, location_id, title, description, is_active, trigger_on_enter, trigger_on_exit, created_at, updated_at) VALUES
(9, 2, 6, 'Study for exam', 'Review chapters 5-8 for midterm', true, true, false, NOW(), NOW()),
(10, 2, 7, 'Submit assignment', 'Data structures assignment due today', true, true, false, NOW(), NOW()),
(11, 2, 7, 'Meet with advisor', 'Thesis consultation at 2 PM', true, true, false, NOW(), NOW()),
(12, 2, 8, 'Work on presentation', 'Prepare slides for final project', true, true, false, NOW(), NOW()),
(13, 2, 9, 'Return books', 'Return borrowed books before due date', true, true, false, NOW(), NOW()),
(14, 2, 9, 'Research for thesis', 'Find references for chapter 2', false, true, false, NOW(), NOW());

-- Reset sequence for reminders
SELECT setval('reminders_id_seq', 14, true);

-- ================================================
-- DONE! Database is now seeded with test data
-- Test credentials:
-- Email: john@example.com | Password: password123
-- Email: jane@example.com | Password: password123
-- ================================================
