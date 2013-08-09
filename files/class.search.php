<?php
    class search {
        private $disallowedWords = array('the', 'and', 'you', 'was', 'for', 'are', 'with', 'they',
            'this', 'have', 'from', 'one', 'had', 'word', 'what', 'were', 'when', 'your', 'said', 'there',
            'each', 'which', 'their', 'will', 'other', 'about', 'many', 'then', 'them', 'some', 'would',
            'make', 'like', 'time', 'look', 'more', 'write', 'number', 'way', 'could', 'people', 'than',
            'first', 'been', 'call', 'who', 'its', 'now', 'find', 'long', 'down', 'day', 'did',
            'get', 'come', 'made', 'may', 'part', 'that');

        public function go($q) {
            $q = trim($q);
            $this->lastSearch = $q;

            $articles = $this->searchArticles($q);
            $users = $this->searchUsers($q);
            return array('articles'=>$articles, 'users'=>$users);
        }

        private function searchArticles($term) {
            global $db;

            // Build search query
            preg_match_all('/(?<!")\b[a-zA-Z0-9"@._-]+\b|(?<=")\b[^"]+/', $term, $terms, PREG_PATTERN_ORDER);
            if (!count($terms[0]))
                return false;

            $searchQuery = '';
            for ($i = 0; $i < count($terms[0]); $i++) {
                $term = strtolower($terms[0][$i]);
                
                //Check if word is valid
                if (strlen($term) <= 2 || in_array($term, $this->disallowedWords))
                    continue;
                
                $searchQuery .= "(( LENGTH(title) - LENGTH(REPLACE(LOWER(title), :{$i}, '')) )
                   / CHAR_LENGTH(:{$i}))*3
                 + (( LENGTH(body) - LENGTH(REPLACE(LOWER(body), :{$i}, '')) )
                   / CHAR_LENGTH(:{$i}))
                 + ";
                $searchValues[":$i"] = $term;
            }
            $searchQuery = substr($searchQuery, 0, -2);
            if (!strlen($searchQuery))
                return false;

            $sql = "SELECT SQL_CALC_FOUND_ROWS a.article_id AS id, users.username, a.title, a.body, a.slug, a.thumbnail,
                        submitted, updated, a.category_id AS cat_id, categories.title AS cat_title, categories.slug AS cat_slug,
                        CONCAT(IF(a.category_id = 0, '/news/', '/articles/'), a.slug) AS uri,
                        search.matches
                    FROM articles a
                    LEFT JOIN articles_categories categories
                    ON a.category_id = categories.category_id
                    LEFT JOIN users
                    ON users.user_id = a.user_id
                    INNER JOIN (
                         SELECT
                            article_id,
                           SUM(
                             {$searchQuery}
                           ) AS matches
                         FROM `articles` 
                         GROUP BY `article_id`
                         HAVING matches > 0
                       ) search
                    ON search.article_id = a.article_id
                    GROUP BY a.article_id
                    ORDER BY search.`matches` DESC, `submitted` DESC
                    LIMIT 5";

            $st = $db->prepare($sql);
            $st->execute($searchValues);
            $result = $st->fetchAll();

            if (!count($result))
                return false;

            return $result;
        }

        private function searchUsers($term) {
            global $db, $user;

            if (strlen($term) <= 3)
                return false;

            $like = "%{$term}%";

            $sql = 'SELECT username, users.score, profile.gravatar, IF (profile.gravatar = 1, users.email , profile.img) as `image`, users_friends.status
                    FROM users
                    LEFT JOIN users_profile as profile
                    ON users.user_id = profile.user_id
                    LEFT JOIN users_friends
                    ON (users_friends.user_id = users.user_id AND users_friends.friend_id = :uid) OR (users_friends.user_id = :uid AND users_friends.friend_id = users.user_id)
                    WHERE username LIKE :like OR email = :term
                    ORDER BY username DESC
                    LIMIT 32';

            $st = $db->prepare($sql);
            $st->execute(array(':like' => $like, ':term' => $term, ':uid' => $user->uid));
            $result = $st->fetchAll();

            if (!count($result))
                return false;

            foreach($result as $res) {
                if (isset($res->image)) {
                    $gravatar = isset($res->gravatar) && $res->gravatar == 1;
                    $res->image = profile::getImg($res->image, 48, $gravatar);
                } else
                    $res->image = profile::getImg(null, 48);
            }

            return $result;
        }

        public function getLastSearchTerm() {
            $result = preg_replace_callback('/(?<!")\b\w+\b|(?<=")\b[^"]+/', array($this, 'lastSearchTermReplace'), $this->lastSearch);

            return $result;
        }

        private function lastSearchTermReplace($matches) {
            $term = $matches[0];

            if (strlen($term) <= 2 || in_array($term, $this->disallowedWords))
                return "<s>$term</s>";
            else
                return $term;
        }
    }
?>