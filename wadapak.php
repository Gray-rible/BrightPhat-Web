-- Admin Approval Status Table
CREATE TABLE admin_approval_status (
    Aprooved_admin_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Aprooved_admin_status ENUM('approved', 'pending', 'rejected') COLLATE utf8mb4_general_ci NULL
);

-- Sample Insert for Admin Approval Status Table
INSERT INTO admin_approval_status (Aprooved_admin_id, Aprooved_admin_status) VALUES
(1, 'pending'),
(2, 'approved'),
(3, 'rejected');

-- Admin Authorizations Table
CREATE TABLE admin_authorizations (
    auth_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NULL,
    authorization_mime VARCHAR(50) COLLATE utf8mb4_general_ci NULL,
    id_proof_mime VARCHAR(50) COLLATE utf8mb4_general_ci NULL,
    authorization_letter VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    id_proof VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    Aprooved_admin_id INT(11) NULL,
    Aprooved_admin TINYINT(1) NULL
);

-- Admin Organizations Table
CREATE TABLE admin_organizations (
    auth_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NULL,
    authorization_mime VARCHAR(50) COLLATE utf8mb4_general_ci NULL,
    id_proof_mime VARCHAR(50) COLLATE utf8mb4_general_ci NULL,
    authorization_letter VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    id_proof VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    Aprooved_admin_id INT(11) NULL,
    Aprooved_admin TINYINT(1) NULL
);

-- Applicants Table
CREATE TABLE applicants (
    applicant_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    scholarship_id INT(11) NOT NULL,
    status ENUM('approved', 'pending', 'rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
    first_name VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    middle_name VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    last_name VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    birthdate DATE NOT NULL,
    place_of_birth VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    mobile_number VARCHAR(15) COLLATE utf8mb4_general_ci NULL,
    email_address VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    password VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    profile_picture BLOB NULL,
    street_barangay VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    town_city_municipality VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    province VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    zip_code VARCHAR(10) COLLATE utf8mb4_general_ci NULL,
    school_attended VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    school_id_number VARCHAR(50) COLLATE utf8mb4_general_ci NULL,
    grade_level VARCHAR(50) COLLATE utf8mb4_general_ci NULL,
    course VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    type_of_disability VARCHAR(255) COLLATE utf8mb4_general_ci NULL
);

-- Requirements Table
CREATE TABLE requirements (
    requirement_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    requirement_name VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    description TEXT COLLATE utf8mb4_general_ci NULL
);

-- Sample Insert for Requirements Table
INSERT INTO requirements (requirement_id, requirement_name, description) VALUES
(1, 'Application Letter', 'A letter of intent, stored as a picture file'),
(2, 'Senior High Grading Report', 'Picture file of senior high grades'),
(3, 'Grades Photocopy', 'Picture file of grades'),
(4, 'Registration Form', 'Picture file of registration'),
(5, 'Supporting Documents', 'Other supporting files'),
(6, 'Medical Certificate', 'Picture file of medical clearance'),
(7, 'Scholarship Application Form 1', 'First application form'),
(8, 'Scholarship Application Form 2', 'Second application form'),
(9, 'Birth Certificate', 'Picture file of birth certificate');

-- Scholarships Table
CREATE TABLE scholarships (
    scholarship_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    scholarship_name VARCHAR(100) COLLATE utf8mb4_general_ci NULL,
    scholarship_category_id INT(11) NULL,
    Scholarship_general_description_s1 TEXT COLLATE utf8mb4_general_ci NULL,
    Scholarship_selection_criteria TEXT COLLATE utf8mb4_general_ci NULL,
    user_id INT(11) NULL,
    status ENUM('active', 'inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
    slots_available INT(11) DEFAULT 0 NULL,
    image_data LONGBLOB NULL,
    Scholarship_education_details_s2 TEXT COLLATE utf8mb4_general_ci NULL,
    Scholarship_financial_assistance_details_s3 TEXT COLLATE utf8mb4_general_ci NULL,
    Scholarship_maintaing_s4 TEXT COLLATE utf8mb4_general_ci NULL,
    Scholarship_effects_for_others_s5 TEXT COLLATE utf8mb4_general_ci NULL,
    forfeiture_of_benefit TEXT COLLATE utf8mb4_general_ci NULL,
    note_for_submission TEXT COLLATE utf8mb4_general_ci NULL
);

-- Scholarship Requirements Table
CREATE TABLE scholarship_requirements (
    scholarship_requirement_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    scholarship_id INT(11) NOT NULL,
    requirement_id INT(11) NOT NULL,
    label VARCHAR(255) COLLATE utf8mb4_general_ci NULL
);

-- User Profiles Table
CREATE TABLE user_profiles (
    user_profile_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NULL,
    profile_avatar LONGBLOB NULL,
    Address VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    Age INT(11) NULL,
    Gender ENUM('male', 'female') COLLATE utf8mb4_general_ci NULL,
    Religion VARCHAR(50) COLLATE utf8mb4_general_ci NULL,
    Nationality VARCHAR(50) COLLATE utf8mb4_general_ci NULL,
    Current_School VARCHAR(100) COLLATE utf8mb4_general_ci NULL,
    Current_School_address VARCHAR(255) COLLATE utf8mb4_general_ci NULL,
    Grade_level VARCHAR(20) COLLATE utf8mb4_general_ci NULL,
    Student_id VARCHAR(20) COLLATE utf8mb4_general_ci NULL
);

-- User Types Table
CREATE TABLE user_types (
    User_type_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    User_type ENUM('admin', 'user') COLLATE utf8mb4_general_ci NULL
);

-- Sample Insert for User Types Table
INSERT INTO user_types (User_type_id, User_type) VALUES
(1, 'user'),
(2, 'admin');
