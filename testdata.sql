-- Some test data.
USE hackthis;

-- Remove any data.
DELETE FROM users;
DELETE FROM users_profile;
DELETE FROM articles_categories;
DELETE FROM articles_draft;
DELETE FROM articles_audit;

-- Add a new user.
INSERT INTO users (user_id, username, password) VALUES(-1, 'Osaka', MD5('pass') );
INSERT INTO users_profile (user_id, name) VALUES(-1, 'Pete');

-- Add a New article
INSERT INTO articles_categories (category_id, title) VALUES (-1, "TEST");
INSERT INTO articles_categories (category_id, title) VALUES (-2, "TESTING");


-- ARTICALS DRAFT
INSERT INTO articles_draft (user_id, title, category_id, body) 
	VALUES(-1, "This is an Article", -1, "HERP DERP.");
-- Edit the article to test the audit triggers.
UPDATE articles_draft SET title = "This is an REDACTED", body = "REDACTED", category_id = -2;

-- ARTICLES

INSERT INTO articles (user_id, title, slug, category_id, body, thumbnail, featured) 
	VALUES(-1, "This is an SUBMITTED Article", "sub-art-woop", -1, "HERP DERP.", "sexy.jpg", 0);
-- Edit the article to test the audit triggers.
UPDATE articles SET 
	title = "This is an SUBMITTED REDACTED"
	, slug = "REDACTED-woop"
	, category_id = -2
	, body = "REDACTED"
	, thumbnail = "safe.jpg"
	, featured = 1;

-- Check The Data.
SELECT '== USER ==';
SELECT user_id, username, password FROM users;

SELECT '== ARTICLES DRAFT ==';
SELECT user_id, category_id, title, body from articles_draft;
SELECT article_id, draft, field, old_value, new_value, time from articles_audit where draft = 1;

SELECT '== ARTICLES ==';
SELECT user_id, title, slug, category_id, body, thumbnail, featured from articles;
SELECT article_id, draft, field, old_value, new_value, time from articles_audit  where draft = 0;

SELECT '== END ==';
