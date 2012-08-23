<?php
/**
 * @package        Blog
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Blog;
class Blog {
	/**
	 * Database index for posts
	 *
	 * @var int
	 */
	private	$posts;
	/**
	 * Database index for comments
	 *
	 * @var int
	 */
	private	$comments;
	/**
	 * Saving indexes of used databases
	 */
	function __construct () {
		global $Config;
		$this->posts	= $Config->module(basename(__DIR__))->db('posts');
		$this->comments	= $Config->module(basename(__DIR__))->db('comments');
	}
	/**
	 * Prepare string to use as path
	 *
	 * @param string	$text
	 *
	 * @return string
	 */
	private function path ($text) {
		return strtr(
			$text,
			[
				' '		=> '_',
				'/'		=> '_',
				'\\'	=> '_'
			]
		);
	}
	/**
	 * Get data of specified post
	 *
	 * @param int			$id
	 *
	 * @return array|bool
	 */
	function get ($id) {
		global $db, $Cache, $L;
		$id	= (int)$id;
		if (($data = $Cache->{'Blog/posts/'.$id.'/'.$L->clang}) === false) {
			$data	= $db->{$this->posts}->qf([
				"SELECT
					`id`,
					`user`,
					`title`,
					`path`,
					`content`
				FROM `[prefix]blog_posts`
				WHERE `id` = '%s'
				LIMIT 1",
				$id
			]);
			if ($data) {
				$data['title']								= $this->ml_process($data['title']);
				$data['path']								= $this->ml_process($data['path']);
				$data['content']							= $this->ml_process($data['content']);
				$data['sections']							= $db->{$this->posts}->qfa(
					"SELECT `section`
					FROM `[prefix]blog_posts_sections`
					WHERE `id` = $id",
					true
				);
				$data['tags']								= $db->{$this->posts}->qfa(
					"SELECT `tag`
					FROM `[prefix]blog_posts_tags`
					WHERE `id` = $id",
					true
				);
				$Cache->{'Blog/posts/'.$id.'/'.$L->clang}	= $data;
			}
		}
		return $data;
	}
	/**
	 * Add new post
	 *
	 * @param string	$title
	 * @param string	$path
	 * @param string	$content
	 * @param int[]		$sections
	 * @param string[]	$tags
	 *
	 * @return bool|int				Id of created post on success of <b>false</> on failure
	 */
	function add ($title, $path, $content, $sections, $tags) {
		global $db, $User;
		$path		= $this->path(str_replace('/', ' ', $path ?: $title));
		$sections	= array_intersect(
			array_keys($this->get_sections_list()),
			$sections
		);
		if (empty($sections)) {
			return false;
		}
		if ($db->{$this->posts}()->q(
			"INSERT INTO `[prefix]blog_posts`
				(`user`, `date`)
			VALUES
				('%s', '%s')",
			$User->id,
			TIME
		)) {
			$id	= $db->{$this->posts}()->id();
			$this->set($id, $title, $path, $content, $sections, $tags);
			return $id;
		} else {
			return false;
		}
	}
	/**
	 * Set data of specified post
	 *
	 * @param int		$id
	 * @param string	$title
	 * @param string	$path
	 * @param string	$content
	 * @param int[]		$sections
	 * @param string[]	$tags
	 *
	 * @return bool
	 */
	function set ($id, $title, $path, $content, $sections, $tags) {
		global $db, $Cache;
		$id			= (int)$id;
		$title		= trim(xap($title));
		$path		= $this->path(str_replace('/', ' ', $path ?: $title));
		$content	= xap($content, true);
		$sections	= array_intersect(
			array_keys($this->get_sections_list()),
			$sections
		);
		if (empty($sections)) {
			return false;
		}
		$sections	= implode(
			',',
			array_map(
				function ($section) use ($id) {
					return "($id, $section)";
				},
				$sections
			)
		);
		$tags		= implode(
			',',
			array_map(
				function ($tag) use ($id) {
					return "($id, $tag)";
				},
				$this->process_tags($tags)
			)
		);
		if ($db->{$this->posts}()->q(
			[
				"DELETE FROM `[prefix]blog_posts_sections`
				WHERE `id` = '%1\$s'",
				"INSERT INTO `[prefix]blog_posts_sections`
					(`id`, `section`)
				VALUES
					$sections",
				"UPDATE `[prefix]blog_posts`
				SET
					`title` = '%s',
					`path` = '%s',
					`content` = '%s'
				WHERE `id` = '%s'
				LIMIT 1",
				"DELETE FROM `[prefix]blog_posts_tags`
				WHERE `id` = '%1\$s'",
				"INSERT INTO `[prefix]blog_posts_tags`
					(`id`, `tag`)
				VALUES
					$tags"
			],
			$this->ml_set('Blog/posts/title', $id, $title),
			$this->ml_set('Blog/posts/path', $id, $path),
			$this->ml_set('Blog/posts/content', $id, $content),
			$id
		)) {
			unset(
				$Cache->{'Blog/posts/'.$id},
				$Cache->{'Blog/sections'}
			);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Delete specified post
	 *
	 * @param int	$id
	 *
	 * @return bool
	 */
	function del ($id) {
		global $db, $Cache;
		$id	= (int)$id;
		if ($db->{$this->posts}()->q(
			"DELETE FROM `[prefix]blog_posts`
			WHERE `id` = '%s'
			LIMIT 1",
			$id
		)) {
			$this->ml_del('Blog/posts/title', $id);
			$this->ml_del('Blog/posts/path', $id);
			$this->ml_del('Blog/posts/content', $id);
			unset(
				$Cache->{'Blog/posts/'.$id},
				$Cache->{'Blog/sections'}
			);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Get total count of posts
	 *
	 * @return int
	 */
	function get_total_count () {
		global $Cache, $db;
		if (($data = $Cache->{'Blog/count'}) === false) {
			$Cache->{'Blog/count'}	= $data	= $db->{$this->posts}->qf(
				"SELECT COUNT(`id`)
				FROM `[prefix]blog_posts`",
				true
			);;
		}
		return $data;
	}
	/**
	 * Get array of sections in form [<i>id</i> => <i>title</i>]
	 *
	 * @return array|bool
	 */
	function get_sections_list () {
		global $Cache, $L;
		if (($data = $Cache->{'Blog/sections/list/'.$L->clang}) === false) {
			$data	= $this->get_sections_list_internal(
				$this->get_sections_structure()
			);
			if ($data) {
				$Cache->{'Blog/sections/list/'.$L->clang}	= $data;
			}
		}
		return $data;
	}
	private function get_sections_list_internal ($structure) {
		if (!empty($structure['sections'])) {
			$list	= [];
			foreach ($structure['sections'] as $section) {
				$list = array_merge(
					$list,
					$this->get_sections_list_internal($section)
				);
			}
			return $list;
		} else {
			return [$structure['id'] => $structure['title']];
		}
	}
	/**
	 * Get array of sections structure
	 *
	 * @return array|bool
	 */
	function get_sections_structure () {
		global $Cache, $L;
		if (($data = $Cache->{'Blog/sections/structure/'.$L->clang}) === false) {
			$data	= $this->get_sections_structure_internal();
			if ($data) {
				$Cache->{'Blog/sections/structure/'.$L->clang}	= $data;
			}
		}
		return $data;
	}
	private function get_sections_structure_internal ($parent = 0) {
		global $db;
		$structure					= [
			'id'	=> $parent,
			'posts'	=> 0
		];
		if ($parent != 0) {
			$structure	= array_merge(
				$structure,
				$this->get_section($parent)
			);
		}
		$sections					= $db->{$this->posts}->qfa([
			"SELECT
				`id`,
				`path`
			FROM `[prefix]blog_sections`
			WHERE `parent` = '%s'",
			$parent
		]);
		$structure['sections']	= [];
		foreach ($sections as $section) {
			$structure['sections'][$section['path']]	= $this->get_sections_structure_internal($section['id']);
		}
		return $structure;
	}
	/**
	 * Get data of specified section
	 *
	 * @param int			$id
	 *
	 * @return array|bool
	 */
	function get_section ($id) {
		global $db, $Cache, $L;
		$id	= (int)$id;
		if (($data = $Cache->{'Blog/sections/'.$id.'/'.$L->clang}) === false) {
			$data											= $db->{$this->posts}->qf([
				"SELECT
					`id`,
					`title`,
					`path`,
					`parent`,
					(
						SELECT COUNT(`id`)
						FROM `[prefix]blog_posts_sections`
						WHERE `section` = '%1\$s'
					) AS `posts`
				FROM `[prefix]blog_sections`
				WHERE `id` = '%1\$s'
				LIMIT 1",
				$id
			]);
			$data['title']									= $this->ml_process($data['title']);
			$data['path']									= $this->ml_process($data['path']);
			$data['full_path']								= [$data['path']];
			$parent											= $data['parent'];
			while ($parent != 0) {
				$section				= $this->get_section($parent);
				$data['full_path'][]	= $section['path'];
				$parent					= $section['parent'];
			}
			$data['full_path']								= implode('/', array_reverse($data['full_path']));
			$Cache->{'Blog/sections/'.$id.'/'.$L->clang}	= $data;
		}
		return $data;
	}
	/**
	 * Add new section
	 *
	 * @param int		$parent
	 * @param string	$title
	 * @param string	$path
	 *
	 * @return bool|int			Id of created section on success of <b>false</> on failure
	 */
	function add_section ($parent, $title, $path) {
		global $db, $Cache;
		$parent	= (int)$parent;
		$path	= $this->path(str_replace('/', ' ', $path ?: $title));
		$posts	= $db->{$this->posts}()->qfa(
			"SELECT `id`
			FROM `[prefix]blog_posts_sections`
			WHERE `section` = $parent"
		);
		if ($db->{$this->posts}()->q(
			"INSERT INTO `[prefix]blog_sections`
				(`parent`)
			VALUES
				($parent)"
		)) {
			$id	= $db->{$this->posts}()->id();
			if ($posts) {
				$db->{$this->posts}()->q(
					"UPDATE `[prefix]blog_posts_sections`
					SET `section` = $id
					WHERE `section` = $parent"
				);
				foreach ($posts as $post) {
					unset($Cache->{'Blog/posts/'.$post});
				}
				unset($post);
			}
			unset($posts);
			$this->set_section($id, $parent, $title, $path);
			unset(
				$Cache->{'Blog/sections/list'},
				$Cache->{'Blog/sections/structure'}
			);
			return $id;
		} else {
			return false;
		}
	}
	/**
	 * Set data of specified section
	 *
	 * @param int		$id
	 * @param int		$parent
	 * @param string	$title
	 * @param string	$path
	 *
	 * @return bool
	 */
	function set_section ($id, $parent, $title, $path) {
		global $db, $Cache, $L;
		$parent	= (int)$parent;
		$title	= trim($title);
		$path	= $this->path(str_replace('/', ' ', $path ?: $title));
		$id		= (int)$id;
		if ($db->{$this->posts}()->q(
			"UPDATE `[prefix]blog_sections`
			SET
				`parent`	= '%s',
				`title`		= '%s',
				`path`		= '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			$parent,
			$this->ml_set('Blog/sections/title', $id, $title),
			$this->ml_set('Blog/sections/path', $id, $path),
			$id
		)) {
			unset(
				$Cache->{'Blog/sections/'.$id},
				$Cache->{'Blog/sections/list'},
				$Cache->{'Blog/sections/structure'}
			);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Delete specified section
	 *
	 * @param int	$id
	 *
	 * @return bool
	 */
	function del_section ($id) {
		global $db, $Cache;
		$id						= (int)$id;
		$parent_section		= $db->{$this->posts}()->qf(
			[
				"SELECT `parent`
				FROM `[prefix]blog_sections`
				WHERE `id` = '%s'
				LIMIT 1",
				$id
			],
			true
		);
		$new_section_for_posts	= $db->{$this->posts}()->qf(
			[
				"SELECT `id`
				FROM `[prefix]blog_sections`
				WHERE
					`parent` = '%s' AND
					`id` != '%s'
				LIMIT 1",
				$parent_section,
				$id
			],
			true
		);
		if ($db->{$this->posts}()->q(
			[
				"UPDATE `[prefix]blog_sections`
				SET `parent` = '%2\$s'
				WHERE `parent` = '%1\$s'",
				"UPDATE IGNORE `[prefix]blog_posts_sections`
				SET `section` = '%3\$s'
				WHERE `section` = '%1\$s'",
				"DELETE FROM `[prefix]blog_posts_sections`
				WHERE `section` = '%1\$s'",
				"DELETE FROM `[prefix]blog_sections`
				WHERE `id` = '%1\$s'
				LIMIT 1"
			],
			$id,
			$parent_section,
			$new_section_for_posts ?: $parent_section
		)) {
			$this->ml_del('Blog/sections/title', $id);
			$this->ml_del('Blog/sections/path', $id);
			unset($Cache->Blog);
			return true;
		} else {
			return false;
		}
	}
	private function ml_process ($text) {
		global $Text;
		return $Text->process($this->posts, $text);
	}
	private function ml_set ($group, $label, $text) {
		global $Text;
		return $Text->set($this->posts, $group, $label, $text);
	}
	private function ml_del ($group, $label) {
		global $Text;
		return $Text->del($this->posts, $group, $label);
	}
	function get_tags_list () {
		global $db, $Cache, $L;
		if (($data = $Cache->{'Blog/tags/'.$L->clang}) === false) {
			$tags	= $db->{$this->posts}->qfa(
				"SELECT
					`id`,
					`text`
				FROM `[prefix]blog_tags`"
			);
			$data	= [];
			if (is_array($tags) && !empty($tags)) {
				foreach ($tags as $tag) {
					$data[$tag['id']]	= $this->ml_process($tag['text']);
				}
				unset($tag);
			}
			unset($tags);
			$Cache->{'Blog/tags/'.$L->clang}	= $data;
		}
		return $data;
	}
	function add_tag ($tag) {
		$tag	= trim(xap($tag));
		if (($id = array_search($tag, $this->get_tags_list())) === false) {
			global $db, $Cache;
			if ($db->{$this->posts}()->q(
				"INSERT INTO `[prefix]blog_tags`
					(`value`)
				VALUES
					('')"
			)) {
				$id	= $db->{$this->posts}()->id();
				$db->{$this->posts}()->q(
					"UPDATE `[prefix]blog_tags`
					SET `value` = '%s'
					WHERE `id` = $id
					LIMIT 1",
					$this->ml_set('Blog/tags', $id, $tag)
				);
				return $id;
			}
			unset($Cache->{'Blog/tags'});
			return false;
		}
		return $id;
	}
	function del_tag ($id) {
		global $db, $Cache;
		$id	= (int)$id;
		if ($db->{$this->posts}()->q(
			[
				"DELETE FROM `[prefix]blog_posts_tags`
				WHERE `tag` = '%s'",
				"DELETE FROM `[prefix]blog_tags`
				WHERE `id` = '%s'"
			],
			$id
		)) {
			$this->ml_del('Blog/tags', $id);
			unset($Cache->{'Blog/tags'});
		}
	}
	private function process_tags ($tags) {
		$tags_list	= $this->get_tags_list();
		$exists		= array_keys($tags_list, $tags);
		$tags		= array_fill_keys($tags, null);
		foreach ($exists as $tag) {
			$tags[$tags_list[$tag]]	= $tag;
		}
		unset($exists);
		foreach ($tags as $tag => &$id) {
			if ($id === null) {
				$id	= $this->add_tag($tag);
			}
		}
		return array_values(array_unique($tags));
	}
}