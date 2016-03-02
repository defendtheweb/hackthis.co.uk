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

        <h1 class='no-margin'>View</h1>
        <div class='dark small'>Filter topics <?=isset($section)?'in this section':'in all sections';?></div>

        <ul class='plain'>
            <li <?=!(isset($_GET['popular']) || isset($_GET['watching']) || isset($_GET['no-replies']))?'class="active"':'';?>><a href='?'>View all</a></li>
            <li <?=isset($_GET['watching'])?'class="active"':'';?>><a href='<?=isset($viewing_thread)?'/forum':'';?>?watching'>Watched threads</a></li>
            <li <?=isset($_GET['popular'])?'class="active"':'';?>><a href='<?=isset($viewing_thread)?'/forum':'';?>?popular'>Most popular threads</a></li>
            <li <?=isset($_GET['no-replies'])?'class="active"':'';?>><a href='<?=isset($viewing_thread)?'/forum':'';?>?no-replies'>Threads with no replies</a></li>
        </ul>


        <?php $stats = $forum->getStats(); ?>
        <h1 class='no-margin'>Stats</h1>

        <span class='forum-stats-label'>Threads</span><?= number_format($stats->threads); ?><br/>
        <span class='forum-stats-label'>Posts</span><?= number_format($stats->posts); ?><br/>
        <span class='forum-stats-label'>Authors</span><?= number_format($stats->members); ?><br/>        
    </div>
</div>