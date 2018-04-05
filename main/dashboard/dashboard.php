<?php
/* For licensing terms, see /license.txt */

/**
 * Template (view in MVC pattern) used for displaying blocks for dashboard.
 *
 * @author Christian Fasanando <christian1827@gmail.com>
 *
 * @package chamilo.dashboard
 */

// protect script
api_block_anonymous_users();

// menu actions for dashboard views
$views = ['blocks', 'list'];

if (isset($_GET['view']) && in_array($_GET['view'], $views)) {
    $dashboard_view = $_GET['view'];
}

$link_blocks_view = $link_list_view = null;

if (isset($dashboard_view) && $dashboard_view == 'list') {
    $link_blocks_view = '<a href="'.api_get_self().'?view=blocks">'.
        Display::return_icon('blocks.png', get_lang('DashboardBlocks'), '', ICON_SIZE_MEDIUM).'</a>';
} else {
    $link_list_view = '<a href="'.api_get_self().'?view=list">'.
        Display::return_icon('edit.png', get_lang('EditBlocks'), '', ICON_SIZE_MEDIUM).'</a>';
}

$configuration_link = null;
if (api_is_platform_admin()) {
    $configuration_link = '<a href="'.api_get_path(WEB_CODE_PATH).'admin/settings.php?category=Plugins">'
    .Display::return_icon('settings.png', get_lang('ConfigureDashboardPlugin'), '', ICON_SIZE_MEDIUM).'</a>';
}

echo '<div class="actions">';
echo $link_blocks_view.$link_list_view.$configuration_link;
echo '</div>';

// block dashboard view
if (isset($dashboard_view) && $dashboard_view == 'blocks') {
    if (isset($blocks) && count($blocks) > 0) {
        $columns = [];
        // group content html by number of column
        if (is_array($blocks)) {
            $tmp_columns = [];
            foreach ($blocks as $block) {
                $tmp_columns[] = $block['column'];
                if (in_array($block['column'], $tmp_columns)) {
                    $columns['column_'.$block['column']][] = $block['content_html'];
                }
            }
        }

        echo '<div id="columns" class="row">';
        if (count($columns) > 0) {
            $columns_name = array_keys($columns);
            // blocks for column 1
            if (in_array('column_1', $columns_name)) {
                echo '<div id="column1" class="col-md-6">';
                foreach ($columns['column_1'] as $content) {
                    echo $content;
                }
                echo '</div>';
            } else {
                echo '<div id="column1" class="col-md-6">';
                echo '&nbsp;';
                echo '</div>';
            }
            // blocks for column 2
            if (in_array('column_2', $columns_name)) {
                // blocks for column 1
                echo '<div id="column2" class="col-md-6">';
                foreach ($columns['column_2'] as $content) {
                    echo $content;
                }
                echo '</div>';
            } else {
                echo '<div id="column2" class="col-md-6">';
                echo '&nbsp;';
                echo '</div>';
            }
        }
        echo '</div>';
    } else {
        echo '<div style="margin-top:20px;">'.get_lang('YouHaveNotEnabledBlocks').'</div>';
    }
} else {
    // block dashboard list
    if (isset($success)) {
        echo Display::return_message(get_lang('BlocksHaveBeenUpdatedSuccessfully'), 'confirm');
    }
    $user_id = api_get_user_id();
    DashboardManager::display_user_dashboard_list($user_id);
}
