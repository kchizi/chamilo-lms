<?php
/* For licensing terms, see /license.txt */

/**
 *	@package chamilo.group
 */
require_once '../inc/global.inc.php';
$this_section = SECTION_COURSES;
$current_course_tool  = TOOL_GROUP;

// Notice for unauthorized people.
api_protect_course_script(true);
$currentUrl = api_get_path(WEB_CODE_PATH).'group/group.php?'.api_get_cidreq();

/*	Create the groups */

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create_groups':
            $groups = array();

            for ($i = 0; $i < $_POST['number_of_groups']; $i ++) {
                $group1['name'] = empty($_POST['group_'.$i.'_name']) ? get_lang('Group').' '.$i : $_POST['group_'.$i.'_name'];
                $group1['category'] = isset($_POST['group_'.$i.'_category']) ? $_POST['group_'.$i.'_category'] : null;
                $group1['tutor'] = isset($_POST['group_'.$i.'_tutor']) ? $_POST['group_'.$i.'_tutor'] : null;
                $group1['places'] = isset($_POST['group_'.$i.'_places']) ? $_POST['group_'.$i.'_places'] : null;
                $groups[] = $group1;
            }

            foreach ($groups as $index => $group) {
                if (!empty($_POST['same_tutor'])) {
                    $group['tutor'] = $_POST['group_0_tutor'];
                }
                if (!empty($_POST['same_places'])) {
                    $group['places'] = $_POST['group_0_places'];
                }
                if (api_get_setting('allow_group_categories') == 'false') {
                    $group['category'] = 0;
                } elseif (isset($_POST['same_category']) && $_POST['same_category']) {
                    $group['category'] = $_POST['group_0_category'];
                }

                GroupManager::create_group(
                    $group['name'],
                    $group['category'],
                    $group['tutor'],
                    $group['places']
                );
            }
            Display::addFlash(Display::return_message(get_lang('GroupsAdded')));
            header("Location: ".$currentUrl);
            exit;
            break;
        case 'create_subgroups':
			GroupManager::create_subgroups(
				$_POST['base_group'],
				$_POST['number_of_groups']
			);
            Display::addFlash(Display::return_message(get_lang('GroupsAdded')));
            header("Location: ".$currentUrl);
            exit;
            break;
        case 'create_class_groups':
            $ids = GroupManager::create_class_groups($_POST['group_category']);
            Display::addFlash(Display::return_message(get_lang('GroupsAdded')));
            header("Location: ".$currentUrl);
            exit;
            break;
    }
}

$nameTools = get_lang('GroupCreation');
$interbreadcrumb[] = array ('url' => 'group.php', 'name' => get_lang('Groups'));
Display :: display_header($nameTools, 'Group');

if (!api_is_allowed_to_edit(false, true)) {
    api_not_allowed();
}

if (isset($_POST['number_of_groups'])) {
    if (!is_numeric($_POST['number_of_groups']) || intval($_POST['number_of_groups']) < 1) {
        Display :: display_error_message(
			get_lang('PleaseEnterValidNumber').'<br /><br />
			<a href="group_creation.php?'.api_get_cidreq().'">&laquo; '.get_lang('Back').'</a>',
			false
		);
    } else {
        $number_of_groups = intval($_POST['number_of_groups']);
        if ($number_of_groups > 1) {
    ?>
    <script>
    var number_of_groups = <?php echo $number_of_groups; ?>;
    function switch_state(key) {
        for( i=1; i<number_of_groups; i++) {
            element = document.getElementById(key+'_'+i);
            element.disabled = !element.disabled;
            disabled = element.disabled;
        }
        ref = document.getElementById(key+'_0');
        if( disabled ) {
            ref.addEventListener("change", copy, false);
        } else {
            ref.removeEventListener("change", copy, false);
        }
        copy_value(key);
    }

    function copy(e) {
        key = e.currentTarget.id;
        var re = new RegExp ('_0', '') ;
        var key = key.replace(re, '') ;
        copy_value(key);
    }

    function copy_value(key) {
        ref = document.getElementById(key+'_0');
        for( i=1; i<number_of_groups; i++) {
            element = document.getElementById(key+'_'+i);
            element.value = ref.value;
        }
    }
    </script>
    <?php
		}
		$group_categories = GroupManager::get_categories();
		$group_id = GroupManager :: get_number_of_groups() + 1;
		$cat_options = [];
		foreach ($group_categories as $index => $category) {
			$cat_options[$category['id']] = $category['title'];
		}
        $form = new FormValidator('create_groups_step2', 'POST', api_get_self().'?'.api_get_cidreq());

		// Modify the default templates
		$renderer = $form->defaultRenderer();
		$form_template = "<form {attributes}>\n<div>\n<table>\n{content}\n</table>\n</div>\n</form>";
		$renderer->setFormTemplate($form_template);
		$element_template = <<<EOT
	<tr>
		<td>
			<!-- BEGIN required -->
			<span class="form_required">*</span> <!-- END required -->{label}
		</td>
		<td>
			<!-- BEGIN error -->
			<span class="form_error">{error}</span><br /><!-- END error -->	{element}
		</td>
	</tr>

EOT;
		$renderer->setCustomElementTemplate($element_template);
        $form->addElement('header', $nameTools);
		$form->addElement('hidden', 'action');
		$form->addElement('hidden', 'number_of_groups');
		$defaults = array();
		// Table heading
		$group_el = array();
		$group_el[] = $form->createElement('static', null, null, '<b>'.get_lang('GroupName').'</b>');

		if (api_get_setting('allow_group_categories') == 'true') {
			$group_el[] = $form->createElement('static', null, null, '<b>'.get_lang('GroupCategory').'</b>');
		}
		$group_el[] = $form->createElement('static', null, null, '<b>'.get_lang('GroupPlacesThis').'</b>');
		$form->addGroup($group_el, 'groups', null, "</td><td>", false);
		// Checkboxes
		if ($_POST['number_of_groups'] > 1) {
			$group_el = array ();
			$group_el[] = $form->createElement('static', null, null, ' ');
			if (api_get_setting('allow_group_categories') == 'true') {
				$group_el[] = $form->createElement('checkbox', 'same_category', null, get_lang('SameForAll'), array('onclick' => "javascript: switch_state('category');"));
			}
			$group_el[] = $form->createElement('checkbox', 'same_places', null, get_lang('SameForAll'), array ('onclick' => "javascript: switch_state('places');"));
			$form->addGroup($group_el, 'groups', null, '</td><td>', false);
		}
		// Properties for all groups
		for ($group_number = 0; $group_number < $_POST['number_of_groups']; $group_number ++) {
			$group_el = array();
			$group_el[] = $form->createElement('text', 'group_'.$group_number.'_name');
			if (api_get_setting('allow_group_categories') == 'true') {
				$group_el[] = $form->createElement('select', 'group_'.$group_number.'_category', null, $cat_options, array('id' => 'category_'.$group_number));
			}
			$group_el[] = $form->createElement('text', 'group_'.$group_number.'_places', null, array('class' => 'span1', 'id' => 'places_'.$group_number));

			if ($_POST['number_of_groups'] < 10000) {
				if ($group_id < 10) {
					$prev = '000';
				} elseif ($group_id < 100) {
					$prev = '00';
				} elseif ($group_id<1000) {
					$prev = '0';
				} else {
					$prev = '';
				}
			}

			$defaults['group_'.$group_number.'_name'] = get_lang('GroupSingle').' '.$prev.$group_id ++;
			$form->addGroup($group_el, 'group_'.$group_number, null, '</td><td>', false);
		}
		$defaults['action'] = 'create_groups';
		$defaults['number_of_groups'] = intval($_POST['number_of_groups']);
		$form->setDefaults($defaults);
		$form->addButtonCreate(get_lang('CreateGroup'), 'submit');
        $form->display();
	}
} else {
	/*
	 * Show form to generate new groups
	 */

	$create_groups_form = new FormValidator('create_groups', 'post', api_get_self().'?'.api_get_cidreq());
	$create_groups_form->addElement('header', $nameTools);
	$group_el = array ();
	$group_el[] = $create_groups_form->createElement('text', 'number_of_groups', array(get_lang('Create'), '1'));
	$group_el[] = $create_groups_form->addButtonCreate(get_lang('ProceedToCreateGroup'), 'submit', true);
	$create_groups_form->addGroup($group_el, 'create_groups', get_lang('NumberOfGroupsToCreate'), ' ', false);
	$defaults = array();
	$defaults['number_of_groups'] = 1;
	$create_groups_form->setDefaults($defaults);
	$create_groups_form->display();

	/*
	 * Show form to generate subgroups
	 */
	if (api_get_setting('allow_group_categories') == 'true' && count(GroupManager :: get_group_list()) > 0) {
		$base_group_options = array ();
		$groups = GroupManager :: get_group_list();
		foreach ($groups as $index => $group) {
			$number_of_students = GroupManager :: number_of_students($group['id']);
			if ($number_of_students > 0) {
				$base_group_options[$group['id']] = $group['name'].' ('.$number_of_students.' '.get_lang('Users').')';
			}
		}
		if (count($base_group_options) > 0) {
			$create_subgroups_form = new FormValidator('create_subgroups', 'post', api_get_self().'?'.api_get_cidreq());
            $create_subgroups_form->addElement('header', get_lang('CreateSubgroups'));
            $create_subgroups_form->addElement('html', get_lang('CreateSubgroupsInfo'));
			$create_subgroups_form->addElement('hidden', 'action');
			$group_el = array();
			$group_el[] = $create_subgroups_form->createElement('static', null, null, get_lang('CreateNumberOfGroups'));
			$group_el[] = $create_subgroups_form->createElement('text', 'number_of_groups', null, array('size' => 3));
			$group_el[] = $create_subgroups_form->createElement('static', null, null, get_lang('WithUsersFrom'));
			$group_el[] = $create_subgroups_form->createElement('select', 'base_group', null, $base_group_options);
			$group_el[] = $create_subgroups_form->createElement('button', 'submit', get_lang('Ok'));
			$create_subgroups_form->addGroup($group_el, 'create_groups', null, ' ', false);
			$defaults = array();
			$defaults['action'] = 'create_subgroups';
			$create_subgroups_form->setDefaults($defaults);
			$create_subgroups_form->display();
		}
	}

	/*
	 * Show form to generate groups from classes subscribed to the course
	 */
    $options['where'] = array(" usergroup.course_id = ? " =>  api_get_real_course_id());
    $obj = new UserGroup();
    $classes = $obj->getUserGroupInCourse($options);
	if (count($classes) > 0) {
		echo '<b>'.get_lang('GroupsFromClasses').'</b>';
		echo '<blockquote>';
		echo '<p>'.get_lang('GroupsFromClassesInfo').'</p>';
		echo '<ul>';
		foreach ($classes as $index => $class) {
			$number_of_users = count($obj->get_users_by_usergroup($class['id']));
			echo '<li>';
			echo $class['name'];
			echo ' ('.$number_of_users.' '.get_lang('Users').')';
			echo '</li>';
		}
		echo '</ul>';

		$create_class_groups_form = new FormValidator('create_class_groups_form', 'post', api_get_self().'?'.api_get_cidreq());
		$create_class_groups_form->addElement('hidden', 'action');
		if (api_get_setting('allow_group_categories') == 'true') {
			$group_categories = GroupManager :: get_categories();
			$cat_options = array();
			foreach ($group_categories as $index => $category) {
				$cat_options[$category['id']] = $category['title'];
			}
			$create_class_groups_form->addElement('select', 'group_category', null, $cat_options);
		} else {
			$create_class_groups_form->addElement('hidden', 'group_category');
		}
		$create_class_groups_form->addElement('submit', 'submit', get_lang('Ok'));
		$defaults['group_category'] = GroupManager::DEFAULT_GROUP_CATEGORY;
		$defaults['action'] = 'create_class_groups';
		$create_class_groups_form->setDefaults($defaults);
		$create_class_groups_form->display();
		echo '</blockquote>';
	}
}

Display :: display_footer();
