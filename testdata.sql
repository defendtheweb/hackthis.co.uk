-- Some test data.
USE hackthis;

-- Add a new user.
INSERT INTO users (user_id, username, password) VALUES(-1, 'Osaka', MD5('pass') );
INSERT INTO users_profile (user_id, name) VALUES(-1, 'Pete');

-- Add a New article
INSERT INTO articles_categories (category_id, title) VALUES (-1, "TEST");
INSERT INTO articles_categories (category_id, title) VALUES (-2, "TESTING");

INSERT INTO articles_draft (user_id, title, category_id, body) 
	VALUES(-1, "This is an Article", -1, "HERP DERP.");
-- Edit the article to test the audit triggers.
UPDATE articles_draft SET title = "This is an REDACTED", body = "REDACTED", category_id = -2;




-- Check The Data.
SELECT '== USER ==';
SELECT user_id, username, password FROM users;

SELECT '== ARTICLES ==';
SELECT user_id, category_id, title, body from articles_draft;
SELECT article_id, draft, field, old_value, new_value, time from articles_audit;

SELECT '== END ==';
