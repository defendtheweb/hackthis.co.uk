<?php
    class notifications {
        public function add($to, $type, $from, $item) {
            global $db;

            $st = $db->prepare('INSERT INTO users_notifications (`user_id`, `type`, `from_id`, `item_id`) VALUES (:to, :type, :from, :item)');
            $result = $st->execute(array(':to' => $to, ':type' => $type, ':from' => $from, ':item' => $item));
           
            return $result;
        }
    }
?>