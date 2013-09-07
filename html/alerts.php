<?php
    $custom_css = array('alerts.scss');
    $page_title = 'Notifications';
    require_once('header.php');

    $event_limit = 25;
    if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0)
        $event_page = (int)$_GET['page'];
    else
        $event_page = 1;

    $event_offset = ($event_page-1) * $event_limit;
    $events = $app->notifications->getEvents($event_limit, $event_offset);

    if (count($events) > 0):
?>

        <ul class='events plain'>
<?php
        foreach ($events->items as $event):
            $event->timestamp = strtotime($event->timestamp);
            if (!isset($prev_day) || $prev_day != date('Ymd', $event->timestamp)):
                if (date('Ymd') == date('Ymd', $event->timestamp))
                    $day = "Today";
                else if (date('Ymd') == date('Ymd', strtotime('-1 day', $event->timestamp)))
                    $day = "Yesterday";
                else
                    $day = date('jS F', $event->timestamp);

                if (isset($prev_day)):
?>
                    </ul>
                </li>
<?php
                endif;
?>
                <li>
                    <h3 class='title white'><?=$day;?></h3>
                    <ul class='plain clearfix'>
<?php
                $prev_day = date('Ymd', $event->timestamp);
            endif;
?>
                        <li <?=($event->seen)?'':'class="new"';?>>
                            <div class='col span_22'>
                                <?=$app->notifications->getText($event);?>
                            </div>
                            <div class='col span_2 dark text-right'>
                                <?=date('H:i', $event->timestamp);?>
                            </div>
                        </li>
<?php
        endforeach;
?>
            </ul>
        </li>
    </ul>

<?php
    else:
?>
    <div class="center">No notifications available</div>
<?php
    endif; 

    if ($events->count > 1) {
        $pagination = new stdClass();
        $pagination->current = $event_page;
        $pagination->count = ceil($events->count/$event_limit);
        $pagination->root = '?page=';
        include('elements/pagination.php');
    }

    require_once('footer.php');
?>           