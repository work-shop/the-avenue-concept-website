<?php

class ameRoleEditor extends ameModule {
	const CORE_COMPONENT_ID = ':wordpress:';

	const UPDATE_PREFERENCES_ACTION = 'ws_ame_rex_update_user_preferences';
	const USER_PREFERENCE_KEY = 'ws_ame_rex_prefs';

	protected $tabSlug = 'roles';
	protected $tabTitle = 'Roles';

	/**
	 * @var ameRexCapability[]
	 */
	private $capabilities = array();
	/**
	 * @var array
	 */
	private $uncategorizedCapabilities = array();

	/**
	 * @var ameRexComponent[]
	 */
	private $knownComponents = array();
	private $postTypeRegistrants = array();
	private $taxonomyRegistrants = array();

	/**
	 * @var ameRexCategory[]
	 */
	private $componentRootCategories = array();

	/**
	 * @var ameRexCategory[]
	 */
	private $componentCapPrefixes = array();

	public function __construct($menuEditor) {
		parent::__construct($menuEditor);

		//Optimization: Only record plugins that register post types and taxonomies when the current page is an AME tab.
		if (isset($_GET['sub_section'])) {
			add_action('registered_post_type', array($this, 'recordPostTypeOrigin'), 10, 2);
			add_action('registered_taxonomy', array($this, 'recordTaxonomyOrigin'), 10, 3);
		}

		add_action('wp_ajax_' . self::UPDATE_PREFERENCES_ACTION, array($this, 'ajaxUpdateUserPreferences'));
	}

	public function enqueueTabScripts() {
		parent::enqueueTabScripts();

		wp_register_auto_versioned_script(
			'ame-rex-dialog-bindings',
			plugins_url('dialog-bindings.js', __FILE__),
			array('knockout', 'jquery', 'jquery-ui-dialog', 'ame-lodash')
		);

		wp_register_auto_versioned_script(
			'ame-role-editor',
			plugins_url('role-editor.js', __FILE__),
			array(
				'ame-lodash',
				'knockout',
				'jquery',
				'jquery-qtip',
				'ame-actor-manager',
				'ame-actor-selector',
				'ame-rex-dialog-bindings',
			)
		);

		wp_enqueue_script('ame-role-editor');

		$this->queryInstalledComponents();

		$defaultCapabilities = $this->getDefaultCapabilities();
		$multisiteCapabilities = $this->getMultisiteOnlyCapabilities();

		foreach ($this->getAllCapabilities(array(wp_get_current_user())) as $capability => $unusedValue) {
			$descriptor = new ameRexCapability();
			if (isset($defaultCapabilities[$capability]) || isset($multisiteCapabilities[$capability])) {
				$descriptor->componentId = self::CORE_COMPONENT_ID;
			} else {
				$this->uncategorizedCapabilities[$capability] = true;
			}
			$this->capabilities[$capability] = $descriptor;
		}
		//TODO: do_not_allow should never end up in a plugin category. It's part of core.

		$postTypes = $this->getPostTypeDescriptors();
		//Record which components use which CPT capabilities.
		foreach ($postTypes as $name => $postType) {
			if (empty($postType['componentId'])) {
				continue;
			}
			$this->knownComponents[$postType['componentId']]->registeredPostTypes[$name] = true;
			foreach ($postType['permissions'] as $action => $capability) {
				if (isset($this->capabilities[$capability])) {
					$this->capabilities[$capability]->usedByComponents[$postType['componentId']] = true;
				}
			}

			//Add a CPT category to the component that created this post type.
			if (empty($postType['isDefault']) && ($postType['componentId'] !== self::CORE_COMPONENT_ID)) {
				$componentRoot = $this->getComponentCategory($postType['componentId']);
				if ($componentRoot) {
					$category = new ameRexPostTypeCategory(
						$postType['label'],
						$postType['componentId'],
						$name,
						$postType['permissions']
					);
					$componentRoot->subcategories[] = $category;
				}
			}
		}

		$taxonomies = $this->findRegisteredTaxonomies();
		//Record taxonomy components and create taxonomy categories for those components.
		foreach ($taxonomies as $name => $taxonomy) {
			if (empty($taxonomy['componentId'])) {
				continue;
			}
			foreach ($taxonomy['permissions'] as $action => $capability) {
				if (isset($this->capabilities[$capability])) {
					$this->capabilities[$capability]->usedByComponents[$taxonomy['componentId']] = true;
				}
			}

			//Add a taxonomy category to the component that created this taxonomy.
			if ($taxonomy['componentId'] !== self::CORE_COMPONENT_ID) {
				$componentRoot = $this->getComponentCategory($taxonomy['componentId']);
				if ($componentRoot) {
					$category = new ameRexTaxonomyCategory(
						$taxonomy['label'],
						$taxonomy['componentId'],
						$name,
						$taxonomy['permissions']
					);
					$componentRoot->subcategories[] = $category;
				}
			}
		}


		//Check which menu items use what capabilities and what the corresponding components are.
		$this->analyseAdminMenuCapabilities();

		$this->queryCapabilityDatabase();

		//Figure out which component each capability belongs to.
		foreach ($this->uncategorizedCapabilities as $capability => $unusedValue) {
			$details = $this->capabilities[$capability];
			if ($details->componentId === self::CORE_COMPONENT_ID) {
				continue;
			}

			if (empty($details['componentId']) && !empty($details['usedByComponents'])) {
				if (count($details['usedByComponents']) > 1) {
					uksort($details->usedByComponents, array($this, 'compareComponents'));
				}
				$details['componentId'] = key($details['usedByComponents']);

				if (isset($details->componentContext[$details->componentId])) {
					$propertiesToCopy = array('permissions', 'documentationUrl');
					foreach ($propertiesToCopy as $property) {
						if (isset($details->componentContext[$details->componentId][$property])) {
							$details->$property = $details->componentContext[$details->componentId][$property];
						}
					}
				}
			}

			if (!empty($details->componentId)) {
				//Add the capability to the component category unless it's already there.
				$category = $this->getComponentCategory($details->componentId);
				if (!$category->hasCapability($capability)) {
					$category->capabilities[$capability] = true;
				}
				unset($this->uncategorizedCapabilities[$capability]);
			}
		}

		$stopWords = $this->getPrefixStopWords();
		foreach ($this->componentRootCategories as $category) {
			//Find the common prefix of each component root, if there is one.
			if (!empty($category->capabilities) && (count($category->capabilities) > 1)) {
				$possiblePrefix = key($category->capabilities);
				foreach ($category->capabilities as $capability => $unusedValue) {
					for ($i = 0; $i < min(strlen($possiblePrefix), strlen($capability)); $i++) {
						if ($possiblePrefix[$i] !== $capability[$i]) {
							if ($i >= 1) {
								$possiblePrefix = substr($possiblePrefix, 0, $i);
							} else {
								$possiblePrefix = '';
							}
							break;
						}
					}

					if ($possiblePrefix === '') {
						break;
					}
				}

				//The prefix must be at least 2 characters long and must not consist entirely of stopwords.
				if (strlen($possiblePrefix) >= 2) {
					$tokens = $this->tokenizeCapability($possiblePrefix);
					$foundStopWords = 0;
					foreach ($tokens as $token) {
						if (isset($stopWords[strtolower($token)])) {
							$foundStopWords++;
						}
					}
					if ($foundStopWords === count($tokens)) {
						continue;
					}

					$prefix = implode(' ', array_slice($tokens, 0, 2));
					$this->componentCapPrefixes[$prefix] = $category;
				}
			}

			//Find component roots that have both subcategories and capabilities and
			//put all freestanding capabilities in a "General" subcategory.
			if (!empty($category->capabilities) && !empty($category->subcategories)) {
				$generalCategory = new ameRexCategory('General', $category->componentId);
				$generalCategory->capabilities = $category->capabilities;
				$category->capabilities = array();
				array_unshift($category->subcategories, $generalCategory);
			}
		}

		$probablePostTypeCategories = $this->findProbablePostTypeCategories();
		$clusteredCategories = $this->groupSimilarCapabilities();

		$coreCategory = $this->loadCoreCategories();

		//Normally, only a Super Admin on Multisite has certain Multisite administration capabilities.
		//However, there is at least one plugin that uses these capabilities even in a regular WP install,
		//so we'll show them as long as they're assigned to at least one role or user.
		if (!is_multisite() && isset($coreCategory->subcategories['default/multisite'])) {
			$multisiteCategory = $coreCategory->subcategories['default/multisite'];
			$multisiteCategory->capabilities = array_intersect_key($multisiteCategory->capabilities, $this->capabilities);
			if (empty($multisiteCategory->capabilities)) {
				unset($coreCategory->subcategories['default/multisite']);
			}
		}

		/*echo '<pre>';
		print_r($clusteredCategories);
		print_r($probablePostTypeCategories);
		print_r(array_keys($this->uncategorizedCapabilities));
		print_r($this->capabilities);
		exit;*/

		$customCategories = array_merge($this->componentRootCategories, $probablePostTypeCategories, $clusteredCategories);
		$customCategoryDescriptors = array();
		foreach ($customCategories as $category) {
			/** @var ameRexCategory $category */
			$customCategoryDescriptors[] = $category->toArray();
		}

		$components = array();
		foreach ($this->knownComponents as $id => $component) {
			$components[$id] = $component->toArray();
		}

		$stableMetaCaps = self::loadCapabilities('stable-meta-caps.txt');
		$metaCapMap = array();
		$currentUserId = get_current_user_id();
		foreach ($stableMetaCaps as $metaCap => $unused) {
			$primitiveCaps = map_meta_cap($metaCap, $currentUserId);
			if ((count($primitiveCaps) === 1) && !in_array('do_not_allow', $primitiveCaps)) {
				$metaCapMap[$metaCap] = reset($primitiveCaps);
			}
		}

		$userPreferences = array();
		$userPreferenceData = get_user_meta(get_current_user_id(), self::USER_PREFERENCE_KEY, true);
		if (is_string($userPreferenceData) && !empty($userPreferenceData)) {
			$userPreferences = json_decode($userPreferenceData, true);
			if (!is_array($userPreferences)) {
				$userPreferences = array();
			}
		}

		$scriptData = array(
			'coreCategory'              => $coreCategory->toArray(),
			'customCategories'          => $customCategoryDescriptors,
			'postTypes'                 => $postTypes,
			'taxonomies'                => $taxonomies,
			'capabilities'              => $this->capabilities,
			'uncategorizedCapabilities' => array_keys($this->uncategorizedCapabilities),
			'deprecatedCapabilities'    => self::loadCapabilities('deprecated-capabilities.txt'),
			'knownComponents'           => $components,
			'metaCapMap'                => $metaCapMap,
			'roles'                     => $this->getRoleData(),
			'users'                     => array(),
			'defaultRoleName'           => get_option('default_role'),
			'trashedRoles'              => array(), //todo: Load trashed roles from somewhere.

			'userPreferences'        => $userPreferences,
			'adminAjaxUrl'           => self_admin_url('admin-ajax.php'),
			'updatePreferencesNonce' => wp_create_nonce(self::UPDATE_PREFERENCES_ACTION),
		);

		wp_add_inline_script(
			'ame-role-editor',
			sprintf('wsRexRoleEditorData = (%s);', json_encode($scriptData))
		);
	}

	public function enqueueTabStyles() {
		parent::enqueueTabStyles();
		wp_enqueue_auto_versioned_style(
			'ame-role-editor-styles',
			plugins_url('role-editor.css', __FILE__)
		);
	}

	private function getPostTypeDescriptors() {
		$results = array();
		$wpPostTypes = get_post_types(array(), 'objects');

		//Normally meta capabilities are not assigned to any roles. We'll skip them unless someone has assigned them.
		$metaCaps = array('edit_post', 'read_post', 'delete_post');

		foreach ($wpPostTypes as $name => $postType) {
			$isIncluded = $postType->public || !$postType->_builtin;

			//Skip the "attachment" post type. It only has one unique capability (upload_files), which
			//is included in a default group.
			if ($name === 'attachment') {
				$isIncluded = false;
			}

			if (!$isIncluded) {
				continue;
			}

			$label = $name;
			$pluralLabel = $name;
			if (isset($postType->labels, $postType->labels->name) && !empty($postType->labels->name)) {
				$label = $postType->labels->name;
				$pluralLabel = $postType->labels->name;
			}

			//We want the plural in lowercase, but if there are multiple consecutive uppercase letters
			//then it's probably an acronym. Stuff like "aBc" is probably a contraction or a proper noun.
			if (!preg_match('@([A-Z]{2}|[a-z][A-Z])@', $pluralLabel)) {
				$pluralLabel = strtolower($pluralLabel);
			}

			$capabilities = array();
			foreach ((array)$postType->cap as $capType => $capability) {
				//Skip meta caps unless they already exist.
				if ($postType->map_meta_cap && in_array($capType, $metaCaps) && !isset($this->capabilities[$capability])) {
					continue;
				}

				//Skip the "read" cap. It's redundant - most CPTs use it, and all roles have it by default.
				if (($capType === 'read') && ($capability === 'read')) {
					continue;
				}

				//Some plugins apparently set capability to "false". Perhaps the intention is to disable it.
				if ($capability === false) {
					continue;
				}

				$capabilities[$capType] = $capability;
			}

			$component = isset($this->postTypeRegistrants[$name]) ? $this->postTypeRegistrants[$name] : null;

			$descriptor = array(
				'label'       => $label,
				'name'        => $name,
				'pluralLabel' => $pluralLabel,
				'permissions' => $capabilities,
				'isDefault'   => isset($postType->_builtin) && $postType->_builtin,
				'componentId' => $component,
			);

			$results[$name] = $descriptor;
		}

		return $results;
	}

	protected function findRegisteredTaxonomies() {
		$registeredTaxonomies = array();
		$usedLabels = array('Categories' => true, 'Category' => true, 'Tags' => true);

		foreach (get_taxonomies(array(), 'object') as $taxonomy) {
			$permissions = (array)($taxonomy->cap);

			//Skip "link_category" because its only cap (manage_links) is already part of a default category.
			if (
				($taxonomy->name === 'link_category')
				&& ($permissions['manage_terms'] === 'manage_links')
				&& (count(array_unique($permissions)) === 1)
			) {
				continue;
			}

			//Skip "nav_menu" and "post_format" because they're intended for internal use and have the same
			//caps as the "Category" taxonomy.
			if (in_array($taxonomy->name, array('nav_menu', 'post_format')) && $taxonomy->_builtin) {
				continue;
			}

			$componentId = null;
			$isBuiltIn = isset($taxonomy->_builtin) && $taxonomy->_builtin;
			if ($isBuiltIn) {
				$componentId = self::CORE_COMPONENT_ID;
			} else if (isset($this->taxonomyRegistrants[$taxonomy->name])) {
				$componentId = $this->taxonomyRegistrants[$taxonomy->name];
			}

			$label = $taxonomy->name;
			if (isset($taxonomy->labels, $taxonomy->labels->name) && !empty($taxonomy->labels->name)) {
				$label = $taxonomy->labels->name;
			}

			$uniqueLabel = $label;
			if (isset($usedLabels[$uniqueLabel]) && !$isBuiltIn) {
				$uniqueLabel = str_replace('_', ' ', $taxonomy->name);
			}
			//We want the label in lowercase unless it's an acronym.
			if (!preg_match('@([A-Z]{2}|[a-z][A-Z])@', $uniqueLabel)) {
				$uniqueLabel = strtolower($uniqueLabel);
			}
			$usedLabels[$uniqueLabel] = true;

			$registeredTaxonomies[$taxonomy->name] = array(
				'name'        => $taxonomy->name,
				'label'       => $label,
				'pluralLabel' => $uniqueLabel,
				'componentId' => $componentId,
				'permissions' => $permissions,
			);

		}

		return $registeredTaxonomies;
	}

	/**
	 * @return ameRexCategory
	 */
	private function loadCoreCategories() {
		$root = new ameRexCategory('Core', self::CORE_COMPONENT_ID);
		$root->slug = 'default/core';

		$lines = file_get_contents(__DIR__ . '/data/core-categories.txt');
		$lines = explode("\n", $lines);

		$currentCategory = new ameRexCategory('Placeholder', self::CORE_COMPONENT_ID);

		//Each category starts with a title. The title is followed by one or more indented lines listing
		//capability names, one capability per line. Blank lines are ignored.
		$lineNumber = 0;
		foreach ($lines as $line) {
			$lineNumber++;

			//Skip blank lines.
			$line = rtrim($line);
			if ($line === '') {
				continue;
			}

			$firstChar = substr($line, 0, 1);
			if ($firstChar === ' ' || $firstChar === "\t") {
				//Found a capability.
				$capability = trim($line);
				//Skip unassigned caps. Even core capabilities sometimes get removed as WP development continues.
				if (isset($this->capabilities[$capability])) {
					$currentCategory->capabilities[$capability] = true;
				}
			} else {
				//Found a "Category title [optional slug]"
				if (preg_match('@^(?P<title>[^\[]+)(?:\s+\[(?P<slug>[^\]]+)\])?\s*$@', $line, $matches)) {
					//Save the previous category if it matched any capabilities.
					if (count($currentCategory->capabilities) > 0) {
						$root->addSubcategory($currentCategory);
					}

					$title = trim($matches['title']);
					$slug = !empty($matches['slug']) ? trim($matches['slug']) : ('default/' . $title);

					$currentCategory = new ameRexCategory($title, self::CORE_COMPONENT_ID);
					$currentCategory->slug = $slug;
				}
			}
		}

		//Save the last category.
		if (count($currentCategory->capabilities) > 0) {
			$root->addSubcategory($currentCategory);
		}

		return $root;
	}

	protected function analyseAdminMenuCapabilities() {
		$menu = $this->menuEditor->get_active_admin_menu();
		if (!empty($menu['tree'])) {
			foreach ($menu['tree'] as $item) {
				$this->analyseMenuItem($item);
			}
		}
	}

	protected function analyseMenuItem($item, $parent = null) {
		$capability = ameUtils::get($item, array('defaults', 'access_level'));

		if (empty($item['custom']) && !empty($item['defaults']) && !empty($capability) && empty($item['separator'])) {
			$defaults = $item['defaults'];
			$hook = get_plugin_page_hook(ameUtils::get($defaults, 'file', ''), ameUtils::get($defaults, 'parent', ''));

			$rawTitle = ameMenuItem::get($item, 'menu_title', '[Untitled]');
			$fullTitle = trim(strip_tags($this->removeUpdateCount($rawTitle)));
			if ($parent) {
				$parentTitle = $this->removeUpdateCount(ameMenuItem::get($parent, 'menu_title', '[Untitled]'));
				$fullTitle = trim(strip_tags($parentTitle)) . ' → ' . $fullTitle;
			}

			$relatedComponents = array();
			if (!empty($hook)) {
				$reflections = $this->getHookReflections($hook);
				foreach ($reflections as $reflection) {
					$path = $reflection->getFileName();
					$componentId = $this->getComponentIdFromPath($path);
					if ($componentId) {
						$relatedComponents[$this->getComponentIdFromPath($path)] = true;
					}
				}
			}

			if (isset($this->capabilities[$capability])) {
				$this->capabilities[$capability]->menuItems[] = $fullTitle;
				$this->capabilities[$capability]->usedByComponents = array_merge(
					$this->capabilities[$capability]->usedByComponents,
					$relatedComponents
				);
			}
		}

		if (!empty($item['items'])) {
			foreach ($item['items'] as $submenu) {
				$this->analyseMenuItem($submenu, $item);
			}
		}
	}

	/**
	 * Remove the number of pending updates or other count elements from a menu title.
	 *
	 * @param string $menuTitle
	 * @return string
	 */
	private function removeUpdateCount($menuTitle) {
		if ((stripos($menuTitle, '<span') === false) || !class_exists('DOMDocument', false)) {
			return $menuTitle;
		}

		/** @noinspection PhpComposerExtensionStubsInspection */
		$dom = new DOMDocument();
		$uniqueId = 'ame-rex-title-wrapper-' . time();
		if (@$dom->loadHTML('<div id="' . $uniqueId . '">' . $menuTitle . '</div>')) {
			/** @noinspection PhpComposerExtensionStubsInspection */
			$xpath = new DOMXpath($dom);
			$result = $xpath->query('//span[contains(@class,"update-plugins") or contains(@class,"awaiting-mod")]');
			if ($result->length > 0) {
				//Remove all matched nodes. We must iterate backwards to prevent messing up the DOMNodeList.
				for ($i = $result->length - 1; $i >= 0; $i--) {
					$span = $result->item(0);
					$span->parentNode->removeChild($span);
				}

				$innerHtml = '';
				$children = $dom->getElementById($uniqueId)->childNodes;
				foreach ($children as $child) {
					$innerHtml .= $child->ownerDocument->saveHTML($child);
				}

				return $innerHtml;
			}
		}
		return $menuTitle;
	}

	/**
	 * @param string $tag
	 * @return AmeReflectionCallable[]
	 */
	protected function getHookReflections($tag) {
		global $wp_filter;
		if (!isset($wp_filter[$tag])) {
			return array();
		}

		$reflections = array();
		foreach ($wp_filter[$tag] as $priority => $handlers) {
			foreach ($handlers as $index => $callback) {
				try {
					$reflection = new AmeReflectionCallable($callback['function']);
					$reflections[] = $reflection;
				} catch (ReflectionException $e) {
					//Invalid callback, let's just ignore it.
					continue;
				}
			}
		}
		return $reflections;
	}

	protected function getComponentIdFromPath($absolutePath) {
		static $pluginDirectory = null, $muPluginDirectory = null, $themeDirectory = null;
		if ($pluginDirectory === null) {
			$pluginDirectory = wp_normalize_path(WP_PLUGIN_DIR);
			$muPluginDirectory = wp_normalize_path(WPMU_PLUGIN_DIR);
			$themeDirectory = wp_normalize_path(WP_CONTENT_DIR . '/themes');
		}

		$absolutePath = wp_normalize_path($absolutePath);
		$pos = null;
		$type = '';
		if (strpos($absolutePath, $pluginDirectory) === 0) {
			$type = 'plugin';
			$pos = strlen($pluginDirectory);
		} else if (strpos($absolutePath, $muPluginDirectory) === 0) {
			$type = 'plugin';
			$pos = strlen($muPluginDirectory);
		} else if (strpos($absolutePath, $themeDirectory) === 0) {
			$type = 'theme';
			$pos = strlen($themeDirectory);
		}

		if ($pos !== null) {
			$nextSlash = strpos($absolutePath, '/', $pos + 1);
			if ($nextSlash !== false) {
				$componentDirectory = substr($absolutePath, $pos + 1, $nextSlash - $pos - 1);
			} else {
				$componentDirectory = substr($absolutePath, $pos + 1);
			}
			return $type . ':' . $componentDirectory;
		}
		return null;
	}

	protected function getRoleData() {
		$wpRoles = ameRoleUtils::get_roles();
		$roles = array();

		$usersByRole = count_users();

		foreach ($wpRoles->role_objects as $roleId => $role) {
			$capabilities = array();
			if (!empty($role->capabilities) && is_array($role->capabilities)) {
				$capabilities = $this->menuEditor->castValuesToBool($role->capabilities);
			}

			$hasUsers = false;
			if (isset($usersByRole['avail_roles'], $usersByRole['avail_roles'][$roleId])) {
				$hasUsers = ($usersByRole['avail_roles'][$roleId] > 0);
			}

			$roles[] = array(
				'name'         => $roleId,
				'displayName'  => ameUtils::get($wpRoles->role_names, $roleId, $roleId),
				'capabilities' => $capabilities,
				'hasUsers'     => $hasUsers,
			);
		}
		return $roles;
	}

	/**
	 * Get a list of all known capabilities that apply to the current WordPress install.
	 *
	 * @param WP_User[] $users List of zero or more users.
	 * @return array Associative array indexed by capability name.
	 */
	protected function getAllCapabilities($users = array()) {
		//Always include capabilities that are built into WordPress.
		$capabilities = $this->getDefaultCapabilities();

		//Add capabilities assigned to roles.
		$capabilities = array_merge($capabilities, ameRoleUtils::get_all_capabilities(is_multisite()));

		//Add capabilities of users.
		$roleNames = ameRoleUtils::get_role_names();
		foreach ($users as $user) {
			$userCaps = $user->caps;
			//Remove roles from the capability list.
			$userCaps = array_diff_key($userCaps, $roleNames);
			$capabilities = array_merge($capabilities, $userCaps);
		}

		$capabilities = $this->menuEditor->castValuesToBool($capabilities);

		//Note: In the future, we could also add custom capabilities here.

		uksort($capabilities, 'strnatcasecmp');
		return $capabilities;
	}

	protected function getDefaultCapabilities() {
		static $defaults = null;
		if ($defaults !== null) {
			return $defaults;
		}

		$defaults = self::loadCapabilities('default-capabilities.txt');

		if (is_multisite()) {
			$defaults = array_merge($defaults, $this->getMultisiteOnlyCapabilities());
		}

		return $defaults;
	}

	protected function getMultisiteOnlyCapabilities() {
		static $cache = null;
		if ($cache === null) {
			$cache = self::loadCapabilities('default-multisite-capabilities.txt');
		}
		return $cache;
	}

	/**
	 * Load a list of capabilities from a text file.
	 *
	 * @param string $fileName
	 * @param bool|int|string $fillValue Optional. Fill the result array with this value. Defaults to false.
	 * @return array Associative array with capability names as keys and $fillValue as values.
	 */
	public static function loadCapabilities($fileName, $fillValue = false) {
		$fileName = __DIR__ . '/data/' . $fileName;
		if (!is_file($fileName) || !is_readable($fileName)) {
			return array();
		}

		$contents = file_get_contents($fileName);

		$capabilities = preg_split('@[\r\n]+@', $contents);
		$capabilities = array_map('trim', $capabilities);
		$capabilities = array_filter($capabilities, array(__CLASS__, 'isNotEmptyString'));
		$capabilities = array_filter($capabilities, array(__CLASS__, 'isNotLineComment'));

		$capabilities = array_fill_keys($capabilities, $fillValue);

		return $capabilities;
	}

	protected static function isNotEmptyString($input) {
		return $input !== '';
	}

	protected static function isLineComment($input) {
		$input = trim($input);
		if ($input === '') {
			return false;
		}

		$firstChar = substr($input, 0, 1);
		if ($firstChar === '#' || $firstChar === ';') {
			return true;
		}

		if (substr($input, 0, 2) === '//') {
			return true;
		}

		return false;
	}

	protected static function isNotLineComment($input) {
		return !self::isLineComment($input);
	}

	/**
	 * Get components, capability metadata and possible categories from the capability database.
	 */
	private function queryCapabilityDatabase() {
		//TODO: This is slow and should be cached when possible.
		//TODO: Get plugin names from their headers, not from the wp.org directory. Directory listings sometimes use marketing names.
		$meta = json_decode(file_get_contents(__DIR__ . '/data/capability-metadata.json'), true);

		foreach ($this->capabilities as $capability => $details) {
			if (isset($meta['capabilities'][$capability])) {
				$capDetails = $meta['capabilities'][$capability];
				if (isset($capDetails['origins'])) {
					$relatedComponents = $capDetails['origins'];
				} else {
					$relatedComponents = $capDetails;
				}

				foreach ($relatedComponents as $origin) {
					if (is_string($origin)) {
						$componentId = $origin;
					} else {
						$componentId = $origin['id'];
						if (count($origin) > 1) {
							$details->componentContext[$componentId] = $origin;
						}
					}
					$details->usedByComponents[$componentId] = true;

					$componentMeta = ameUtils::get($meta, 'components.' . $componentId, array());
					if (!isset($this->knownComponents[$componentId])) {
						$this->knownComponents[$componentId] = new ameRexComponent(
							$componentId,
							ameUtils::get($componentMeta, 'name')
						);
					}
					if (isset($componentMeta['activeInstalls'])) {
						$this->knownComponents[$componentId]->activeInstalls = $componentMeta['activeInstalls'];
					}
					if (isset($componentMeta['capabilityDocumentationUrl'])) {
						$this->knownComponents[$componentId]->capabilityDocumentationUrl = $componentMeta['capabilityDocumentationUrl'];
					}
				}
			}
		}
	}

	private function compareComponents($idA, $idB) {
		$a = $this->knownComponents[$idA];
		$b = $this->knownComponents[$idB];

		if (!empty($a->isActive) && empty($b->isActive)) {
			return -1;
		}
		if (!empty($b->isActive) && empty($a->isActive)) {
			return 1;
		}
		if (!empty($a->isInstalled) && empty($b->isInstalled)) {
			return -1;
		}
		if (!empty($b->isInstalled) && empty($a->isInstalled)) {
			return 1;
		}

		return ($b->activeInstalls - $a->activeInstalls);
	}

	/**
	 * @param string $id
	 * @param WP_Post_Type $postType
	 */
	public function recordPostTypeOrigin($id, $postType) {
		if (!is_admin() || empty($postType) || empty($id)) {
			return;
		}

		if (isset($postType->_builtin) && $postType->_builtin) {
			return;
		}

		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		//Drop the first two entries because they just contain this method and an apply_filters or do_action call.
		array_shift($trace);
		array_shift($trace);

		//Find the last entry that is part of a plugin or theme.
		$component = $this->detectCallerComponent();
		if ($component !== null) {
			$this->postTypeRegistrants[$id] = $component;
		}
	}

	public function recordTaxonomyOrigin(
		$id,
		/** @noinspection PhpUnusedParameterInspection It's part of the filter signature. We can't remove it. */
		$objectType,
		$taxonomy = array()
	) {
		if (!is_admin() || empty($taxonomy) || empty($id) || !is_array($taxonomy)) {
			return;
		}

		if (isset($taxonomy['_builtin']) && $taxonomy['_builtin']) {
			return;
		}

		$component = $this->detectCallerComponent();
		if ($component !== null) {
			$this->taxonomyRegistrants[$id] = $component;
		}
	}

	/**
	 * Detect the plugin or theme that triggered the current hook.
	 * If multiple components are involved, only the earliest one will be returned
	 * (i.e. the one at the bottom of the call stack).
	 *
	 * @return null|string
	 */
	private function detectCallerComponent() {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		//Drop the first two entries because they just contain this method and an apply_filters or do_action call.
		array_shift($trace);
		array_shift($trace);

		//Find the last entry that is part of a plugin or theme.
		$component = null;
		foreach ($trace as $item) {
			if (empty($item['file'])) {
				continue;
			}

			$possibleComponent = $this->getComponentIdFromPath($item['file']);
			if ($possibleComponent) {
				$component = $possibleComponent;
			}
		}
		return $component;
	}

	private function queryInstalledComponents() {
		$installedPlugins = get_plugins();
		foreach ($installedPlugins as $pluginFile => $plugin) {
			$pathComponents = explode('/', $pluginFile, 2);
			if (count($pathComponents) < 2) {
				continue;
			}
			$component = new ameRexComponent(
				'plugin:' . $pathComponents[0],
				ameUtils::get($plugin, 'Name', $pathComponents[0])
			);
			$component->isInstalled = true;
			$component->isActive = is_plugin_active($pluginFile);
			$this->knownComponents[$component->id] = $component;
		}

		$activeThemeSlugs = array(get_stylesheet(), get_template());
		foreach ($activeThemeSlugs as $slug) {
			$componentId = 'theme:' . $slug;
			if (isset($this->knownComponents[$componentId])) {
				continue;
			}
			$theme = wp_get_theme($slug);
			if (!empty($theme)) {
				$component = new ameRexComponent($componentId, $theme->get('Name'));
				$component->isActive = true;
				$component->isInstalled = true;
				$this->knownComponents[$component->id] = $component;
			}
		}
	}

	/**
	 * @param $componentId
	 * @return ameRexCategory|null
	 */
	private function getComponentCategory($componentId) {
		if (!isset($this->componentRootCategories[$componentId])) {
			if (!isset($this->knownComponents[$componentId])) {
				return null;
			}
			$category = new ameRexCategory($this->knownComponents[$componentId]->name, $componentId);
			$category->slug = 'components/' . $componentId;
			$this->componentRootCategories[$componentId] = $category;
		}
		return $this->componentRootCategories[$componentId];
	}

	/**
	 * Group capabilities that look like they belong to a post type but are not used by any registered post types.
	 * This could be stuff left behind by an uninstalled plugin, or just a set of similar capabilities.
	 *
	 * @return ameRexCategory[]
	 */
	protected function findProbablePostTypeCategories() {
		$potentialPostTypes = array();
		$foundCategories = array();

		//At the moment, WordPress database schema limits post types to 20 characters.
		$namePattern = '(?P<post_type>.{1,20}?)s?';
		$cptPatterns = array(
			'@^edit_(?:(?:others|private|published)_)?' . $namePattern . '$@',
			'@^delete_(?:(?:others|private|published)_)?' . $namePattern . '$@',
			'@^publish_' . $namePattern . '$@',

			'@^read_private_' . $namePattern . '$@',
			'@^read_' . $namePattern . '$@',

			//WooCommerce stuff
			'@^(assign|edit|manage|delete)_' . $namePattern . '_terms$@',
		);

		foreach ($this->uncategorizedCapabilities as $capability => $unused) {
			foreach ($cptPatterns as $pattern) {
				if (preg_match($pattern, $capability, $matches)) {
					$postType = $matches['post_type'];

					//Unknown CPT-like capability.
					if (!isset($potentialPostTypes[$postType])) {
						$potentialPostTypes[$postType] = array();
					}
					$potentialPostTypes[$postType][$capability] = $capability;

					break;
				}
			}
		}

		//Empirically, real post types have at least 3 associated capabilities.
		foreach ($potentialPostTypes as $postType => $typeCaps) {
			if (count($typeCaps) >= 3) {
				//Note that this group does not correspond to an existing post type. It's just a set of similar caps.
				$title = ucwords(str_replace('_', ' ', $postType));
				if (substr($title, -1) !== 's') {
					$title .= 's'; //Post type titles are usually plural.
				}

				$category = new ameRexCategory($title);
				$category->capabilities = array_fill_keys($typeCaps, true);
				$foundCategories[] = $category;

				//Now that we know which group these caps belong to, remove them from consideration.
				foreach ($typeCaps as $capability) {
					unset($this->uncategorizedCapabilities[$capability]);
				}
			}
		}

		return $foundCategories;
	}

	private function groupSimilarCapabilities() {
		$stopWords = $this->getPrefixStopWords();

		$possibleCategories = array();
		foreach ($this->uncategorizedCapabilities as $capability => $unusedValue) {
			$tokens = $this->tokenizeCapability($capability);
			$upperLimit = min(2, count($tokens) - 1);

			$prefix = null;
			for ($i = 0; $i < $upperLimit; $i++) {
				if ($prefix === null) {
					$prefix = $tokens[$i];
				} else {
					$prefix .= ' ' . $tokens[$i];
				}
				if (isset($stopWords[$tokens[$i]]) || (strlen($tokens[$i]) < 2)) {
					continue;
				}

				//Check if one of the existing component categories has the same prefix
				//and add this capability there.
				if (isset($this->componentCapPrefixes[$prefix])) {
					$this->componentCapPrefixes[$prefix]->addCapabilityToDefaultLocation($capability);
					unset($this->uncategorizedCapabilities[$capability]);

					$componentId = $this->componentCapPrefixes[$prefix]->componentId;
					if ($componentId !== null) {
						$this->capabilities[$capability]->usedByComponents[$componentId] = true;
						if (empty($this->capabilities[$capability]->componentId)) {
							$this->capabilities[$capability]->componentId = $componentId;
						}
					}
				}

				if (!isset($possibleCategories[$prefix])) {
					$possibleCategories[$prefix] = array();
				}
				$possibleCategories[$prefix][$capability] = true;
			}
		}

		uasort($possibleCategories, array($this, 'compareArraySizes'));

		$approvedCategories = array();
		foreach ($possibleCategories as $prefix => $capabilities) {
			$capabilities = array_intersect_key($capabilities, $this->uncategorizedCapabilities);
			if (count($capabilities) < 3) {
				continue;
			}

			$title = $prefix;
			//Convert all-lowercase to Title Case, but preserve stuff that already has mixed case.
			if (strtolower($title) === $title) {
				$title = ucwords($title);
			}

			//No vowels = probably an acronym.
			if (!preg_match('@[aeuio]@', $title)) {
				$title = strtoupper($title);
			}

			$category = new ameRexCategory($title);
			$category->capabilities = $capabilities;
			$approvedCategories[] = $category;
			foreach ($capabilities as $capability => $unused) {
				unset($this->uncategorizedCapabilities[$capability]);
			}
		}

		return $approvedCategories;
	}

	private function tokenizeCapability($capability) {
		return preg_split('@[\s_\-]@', $capability, -1, PREG_SPLIT_NO_EMPTY);
	}

	private function getPrefixStopWords() {
		static $stopWords = null;
		if ($stopWords === null) {
			$stopWords = array(
				'edit',
				'delete',
				'add',
				'list',
				'manage',
				'read',
				'others',
				'private',
				'published',
				'publish',
				'terms',
				'view',
				'create',
				'settings',
				'options',
				'option',
				'setting',
				'update',
				'install',
			);
			$stopWords = array_fill_keys($stopWords, true);
		}
		return $stopWords;
	}

	private function compareArraySizes($a, $b) {
		return count($b) - count($a);
	}

	public function ajaxUpdateUserPreferences() {
		check_ajax_referer(self::UPDATE_PREFERENCES_ACTION);

		@header('Content-Type: application/json; charset=' . get_option('blog_charset'));
		if (!$this->menuEditor->current_user_can_edit_menu() || !current_user_can('edit_users')) {
			echo json_encode(array('error' => 'Access denied'));
			exit;
		}

		$post = $this->menuEditor->get_post_params();
		if (!isset($post['preferences']) || !is_string($post['preferences'])) {
			echo json_encode(array('error' => 'The "preferences" field is missing or invalid.'));
			exit;
		}

		$preferences = json_decode($post['preferences'], true);
		if ($preferences === null) {
			echo json_encode(array('error' => 'The "preferences" field is not valid JSON.'));
			exit;
		}

		if (!is_array($preferences)) {
			echo json_encode(array('error' => 'The "preferences" field is not valid. Expected an associative array.'));
			exit;
		}

		update_user_meta(get_current_user_id(), self::USER_PREFERENCE_KEY, json_encode($preferences));

		echo json_encode(array('success' => true));
		exit;
	}
}