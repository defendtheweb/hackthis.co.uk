<?php
    $page_title = 'Admin - Sitemap';
    define("PAGE_PRIV", "admin_site");

    require_once('header.php');


    $query = "SELECT * FROM forum_sections WHERE parent_id IN (6,7,8,9,10,11,12,13)";

    $st = $app->db->prepare($query);
    $st->execute();
    $sections = $st->fetchAll();

    foreach ($sections AS $section) {
        $slug = $section->slug . '/solutions';
        $title = 'Solutions';
        $description = "Discuss how you completed " . $section->title;
        $parent_id = $section->section_id;

        if ($section->title == 'Xmas') {
            $priv_level = '45';
        } else {
            $tmpTitle = explode(' ', $section->title);
            $query = "SELECT * FROM levels WHERE `group` = '" . $tmpTitle[0] . "' AND `name` = '" . $tmpTitle[2] . "'";
            $st = $app->db->prepare($query);
            $st->execute();
            $level = $st->fetch();

            $priv_level = $level->level_id;
        }

        if (!$priv_level) {
            continue;
        }

        echo $priv_level;

        echo $description . "<br/>";

        $query = "INSERT INTO forum_sections (`parent_id`, `title`, `slug`, `description`, `priv_level`) VALUES (:pid, :t, :s, :d, :p)";
        $st = $app->db->prepare($query);
        $st->execute(array(':pid'=>$parent_id, ':t'=>$title, ':s'=>$slug, ':d'=>$description, ':p'=>$priv_level));
    }


    require_once('footer.php');
?>