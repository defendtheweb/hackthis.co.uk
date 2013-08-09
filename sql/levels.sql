USE hackthis;

-- Add categories
INSERT INTO levels_groups (`title`, `order`) VALUES ('Main', 1), ('Basic+', 2), ('Intermediate', 3), ('Javascript', 4), ('SQLi', 5), ('Coding', 6), ('Crypt', 7), ('Real', 8);
-- Main levels
INSERT INTO levels (`name`, `group`) VALUES ('1', 'main'), ('2', 'main'), ('3', 'main'), ('4', 'main'), ('5', 'main');
-- Basic+ levels
INSERT INTO levels (`name`, `group`) VALUES ('1', 'Basic+'), ('2', 'Basic+'), ('3', 'Basic+'), ('4', 'Basic+'), ('5', 'Basic+'), ('X', 'Basic+');
-- Intermediate levels
INSERT INTO levels (`name`, `group`) VALUES ('1', 'Intermediate'), ('2', 'Intermediate'), ('3', 'Intermediate');