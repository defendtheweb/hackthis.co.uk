<div class="col span_6 forum-sidebar">
    <div class='sticky'>
<?php 
    if ((isset($section) && $section) || isset($article)):
?>
        <a class='button' href='/forum/'><i class='icon-caret-left'></i> Forum Index</a>
        <br/><br/>
<?php endif; ?>
        <h1>Sections</h1>
        <ul class='sections'>
<?php
            $sections = $forum->getSections(null);

            foreach($sections as $sec) {
                $forum->printSectionsList($sec, true, $section);
            }
?>
        </ul>

        <h1>View</h1>
        <ul class='plain'>
            <li <?=!(isset($_GET['popular']) || isset($_GET['no-replies']))?'class="active"':'';?>><a href='?'>View all</a></li>
            <li <?=isset($_GET['popular'])?'class="active"':'';?>><a href='<?=isset($viewing_thread)?'/forum':'';?>?popular'>Most popular threads</a></li>
            <li <?=isset($_GET['no-replies'])?'class="active"':'';?>><a href='<?=isset($thread)?'/forum':'';?>?no-replies'>Threads with no replies</a></li>
        </ul>

<?php
    include('widgets/adverts.php');
?>
    </div>
</div>