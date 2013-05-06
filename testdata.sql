-- Some test data.
USE hackthis;

-- Remove any data.
DELETE FROM users;
DELETE FROM users_profile;
DELETE FROM articles_categories;
DELETE FROM articles_draft;
DELETE FROM articles_audit;

-- Add a new user.
INSERT INTO users (`username`, `password`) VALUES ('Osaka', SHA1('pass'));
INSERT INTO users_profile (`user_id`, `name`) VALUES (1, 'Pete');

INSERT INTO users (`username`, `password`) VALUES ('flabbyrabbit', SHA1('pass') );
INSERT INTO users (`username`, `password`) VALUES ('verath', SHA1('pass') );
INSERT INTO users (`username`, `password`) VALUES ('daMage', SHA1('pass') );

-- Medals
INSERT INTO medals_colours (`reward`, `colour`) VALUES (100, 'bronze');
INSERT INTO medals_colours (`reward`, `colour`) VALUES (200, 'silver');
INSERT INTO medals (`label`, `colour_id`, `description`) VALUES ('Test', 1, 'Test');
INSERT INTO medals (`label`, `colour_id`, `description`) VALUES ('Test', 2, 'Test');

-- Award medal
INSERT INTO users_medals (`user_id`, `medal_id`) VALUES (1, 1);
INSERT INTO users_medals (`user_id`, `medal_id`) VALUES (2, 1);
INSERT INTO users_medals (`user_id`, `medal_id`) VALUES (2, 2);

-- Send friend request
INSERT INTO users_friends (`user_id`, `friend_id`) VALUES (1, 2);
INSERT INTO users_friends (`user_id`, `friend_id`) VALUES (3, 2);
INSERT INTO users_friends (`user_id`, `friend_id`) VALUES (2, 4);
UPDATE users_friends SET status = 1 WHERE user_id = '2' AND friend_id = '4';


-- Send pm
INSERT INTO pm (`title`) VALUES ('YO');
INSERT INTO pm_users (`pm_id`, `user_id`) VALUES (1, 1);
INSERT INTO pm_users (`pm_id`, `user_id`) VALUES (1, 2);
INSERT INTO pm_messages (`pm_id`, `user_id`, `message`) VALUES (1, 1, 'Hello young sir');


-- Add a New article
INSERT INTO articles_categories (category_id, title) VALUES (1, "TEST");
INSERT INTO articles_categories (category_id, title) VALUES (2, "TESTING");


-- ARTICALS DRAFT
INSERT INTO articles_draft (user_id, title, category_id, body) 
	VALUES(1, "This is an Article", 1, "HERP DERP.");
-- Edit the article to test the audit triggers.
UPDATE articles_draft SET title = "This is an REDACTED", body = "REDACTED", category_id = 2;

-- ARTICLES
INSERT INTO articles (user_id, title, slug, category_id, body, thumbnail, featured) 
	VALUES(1, "This is an SUBMITTED Article", "sub-art-woop", 1, "HERP DERP.", "sexy.jpg", 0);

-- Edit the article to test the audit triggers.
UPDATE articles SET 
	title = "This is an SUBMITTED REDACTED"
	, slug = "REDACTED-woop"
	, category_id = 2
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