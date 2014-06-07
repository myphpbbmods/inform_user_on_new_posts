<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$user->setup('posting');

// Grab only parameters needed here
$post_id			= request_var('p', 0);
$topic_id			= request_var('t', 0);
$forum_id			= request_var('f', 0);
$topic_cur_post_id	= request_var('topic_cur_post_id', 0);

if (!$topic_id)
{
	trigger_error('NO_TOPIC');
}

// Force forum id
$sql = 'SELECT forum_id
FROM ' . TOPICS_TABLE . '
WHERE topic_id = ' . $topic_id;
$result = $db->sql_query($sql);
$f_id = (int) $db->sql_fetchfield('forum_id');
$db->sql_freeresult($result);

$forum_id = (!$f_id) ? $forum_id : $f_id;

$sql = 'SELECT f.*, t.*
FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
WHERE t.topic_id = $topic_id
	AND (f.forum_id = t.forum_id
		OR f.forum_id = $forum_id)" .
(($auth->acl_get('m_approve', $forum_id)) ? '' : 'AND t.topic_approved = 1');

$result = $db->sql_query($sql);
$post_data = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$post_data)
{
	trigger_error(($mode == 'post' || $mode == 'bump' || $mode == 'reply') ? 'NO_TOPIC' : 'NO_POST');
}

if ($topic_cur_post_id && $topic_cur_post_id != $post_data['topic_last_post_id'])
{
	if ($post_data['forum_flags'] & FORUM_FLAG_POST_REVIEW)
	{
		print $user->lang['POST_REVIEW_EXPLAIN'];
	}
}