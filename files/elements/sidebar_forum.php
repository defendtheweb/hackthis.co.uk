<div class="col span_6 forum-sidebar">
    <div class='sticky'>
<?php if ((isset($section) && $section) || isset($article)): ?>
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

<?php
    include('widgets/adverts.php');
?>
    </div>
</div>