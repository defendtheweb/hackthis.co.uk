-- Some test data.
USE hackthis;

-- Add a new user.
INSERT INTO users (user_id, username, password) VALUES(-1, 'Osaka', MD5('pass') );
INSERT INTO users_profile (user_id, name) VALUES(-1, 'Pete');
