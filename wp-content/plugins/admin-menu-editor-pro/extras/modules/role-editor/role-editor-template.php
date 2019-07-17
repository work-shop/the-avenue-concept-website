<?php require AME_ROOT_DIR . '/modules/actor-selector/actor-selector-template.php'; ?>

<div id="ame-role-editor-root">
	<div data-bind="visible: !isLoaded()" id="rex-loading-message">Loading...</div>

	<div id="rex-main-ui" data-bind="visible: isLoaded" style="display: none;">
		<div id="rex-content-container">

			<div id="rex-category-sidebar">
				<div class="rex-dropdown-trigger"
				     data-target-dropdown-id="rex-category-list-options">
					<div class="dashicons dashicons-admin-generic"></div>
				</div>

				<ul data-bind="template: {name: 'rex-nav-item-template', data: rootCategory}"
				    id="rex-category-navigation"></ul>
			</div>

			<div id="rex-capability-view-container">
				<div id="rex-view-toolbar">
					<input type="search" title="Filter capabilities" placeholder="Search" id="rex-quick-search-query"
					       data-bind="textInput: searchQuery">

					<label>
						<input type="checkbox" data-bind="checked: readableNamesEnabled"> Readable names
					</label>
					<label>
						<input type="checkbox" data-bind="checked: showRedundantEnabled"> Show redundant
					</label>

					<select id="rex-category-view-selector" title="Choose the category view"
					        data-bind="
					            options: categoryViewOptions,
								optionsText: 'label',
								value: categoryViewMode">
					</select>
				</div>
				<div id="rex-capability-view" data-bind="css: {
						'rex-readable-names-enabled': readableNamesEnabled
					}, template: categoryViewMode().templateName">
				</div>
			</div>
		</div>

		<div id="rex-action-sidebar">
			<?php
			submit_button(
				'Save Changes',
				'primary rex-action-button',
				'rex-save-changes-button',
				false,
				array('data-bind' => '')
			);
			?>
			<div class="rex-action-separator"></div>
			<?php
			submit_button(
				'Add role',
				'rex-action-button',
				'rex-add-role-button',
				false,
				array('data-bind' => 'rexOpenDialog: "#rex-add-role-dialog"')
			);

			submit_button(
				'Rename role',
				'rex-action-button',
				'rex-rename-role-button',
				false,
				array('data-bind' => 'rexOpenDialog: "#rex-rename-role-dialog"')
			);

			submit_button(
				'Delete role',
				'rex-action-button',
				'rex-delete-role-button',
				false,
				array('data-bind' => 'rexOpenDialog: "#rex-delete-role-dialog"')
			);
			?>
			<div class="rex-action-separator"></div>
			<?php
			submit_button(
				'Add capability',
				'rex-action-button',
				'rex-add-capability-button',
				false,
				array('data-bind' => 'rexOpenDialog: "#rex-add-capability-dialog"')
			);

			submit_button(
				'Delete capability',
				'rex-action-button',
				'rex-delete-capability-button',
				false,
				array('data-bind' => 'rexOpenDialog: "#rex-delete-capability-dialog"')
			);
			?>
		</div>

	</div>

	<div id="rex-category-list-options" class="rex-dropdown" style="display: none;">
		<label class="rex-dropdown-item">
			<input type="checkbox" data-bind="checked: showNumberOfCapsEnabled"> Show number of capabilities
		</label>
		<label class="rex-dropdown-item rex-dropdown-sub-item">
			<input type="checkbox" data-bind="checked: showGrantedCapCountEnabled, enable: showNumberOfCapsEnabled">
			Show granted
		</label>
		<label class="rex-dropdown-item rex-dropdown-sub-item">
			<input type="checkbox" data-bind="checked: showTotalCapCountEnabled, enable: showNumberOfCapsEnabled"> Show
			total
		</label>
		<label class="rex-dropdown-item rex-dropdown-sub-item">
			<input type="checkbox" data-bind="checked: showZerosEnabled, enable: showNumberOfCapsEnabled"> Show zeros
		</label>
	</div>

	<!-- Permission tooltip content -->
	<div style="display: none;">
		<div id="rex-permission-tip" data-bind="if: permissionTipSubject">
			<div class="rex-permission-description"
			     data-bind="if: permissionTipSubject().mainDescription">
				<span data-bind="text: permissionTipSubject().mainDescription"></span>
			</div>
			<code data-bind="text: permissionTipSubject().capability.name"></code>

			<div class="rex-tooltip-section-container">
				<!-- ko if: (selectedActor() && selectedActor().canHaveRoles) -->
				<div class="rex-tooltip-section">
					<h4>Inheritance</h4>
					<table class="widefat rex-capability-inheritance-breakdown">
						<tbody
							data-bind="foreach: selectedActor().getInheritanceDetails(permissionTipSubject().capability)">
						<tr data-bind="css: {'rex-is-decisive-actor': isDecisive}">
							<td data-bind="text: name" class="rex-inheritance-actor-name"></td>
							<td data-bind="text: description"></td>
						</tr>
						</tbody>
					</table>
				</div>
				<!-- /ko -->

				<!-- ko if: permissionTipSubject().capability.notes -->
				<div class="rex-tooltip-section">
					<h4>Notes</h4>
					<span data-bind="text: permissionTipSubject().capability.notes"></span>
				</div>
				<!-- /ko -->

				<!-- ko if: permissionTipSubject().capability.grantedPermissions().length > 0 -->
				<div class="rex-tooltip-section">
					<h4>Permissions</h4>
					<ul data-bind="foreach: permissionTipSubject().capability.grantedPermissions()"
					    class="rex-tip-granted-permissions">
						<li data-bind="text: $data"></li>
					</ul>
				</div>
				<!-- /ko -->

				<div data-bind="if: permissionTipSubject().capability.originComponent" class="rex-tooltip-section">
					<h4>Origin</h4>
					<span data-bind="text: permissionTipSubject().capability.originComponent.name"></span>
				</div>

				<div data-bind="if: permissionTipSubject().capability.getDocumentationUrl()"
				     class="rex-tooltip-section">
					<h4>See also</h4>
					<span>
						<a href="#"
						   target="_blank"
						   class="rex-documentation-link"
						   data-bind="text: permissionTipSubject().capability.getDocumentationUrl(),
					    attr: {href: permissionTipSubject().capability.getDocumentationUrl()}"></a>
					</span>
				</div>
			</div>
		</div>
	</div>

	<div id="rex-delete-capability-dialog"
	     data-bind="rexDialog: deleteCapabilityDialog, rexEnableDialogButton: deleteCapabilityDialog.isDeleteButtonEnabled"
	     title="Delete capability"
	     style="display: none;" class="rex-dialog">
		<p class="rex-dialog-section">
			Select capabilities to remove from all roles:
		</p>

		<div class="rex-deletable-capability-container"
		     data-bind="visible: (deleteCapabilityDialog.deletableItems().length > 0)">
			<ul data-bind="foreach: deleteCapabilityDialog.deletableItems" class="rex-deletable-capability-list">
				<li>
					<label>
						<input type="checkbox" data-bind="checked: isSelected">
						<span data-bind="text: capability.displayName" class="rex-capability-name"></span>
					</label>
				</li>
			</ul>
		</div>

		<p class="rex-dialog-section" data-bind="visible: (deleteCapabilityDialog.deletableItems().length <= 0)">
			There are no custom capabilities that can be deleted.
		</p>
	</div>

	<div id="rex-add-capability-dialog"
	     data-bind="rexDialog: addCapabilityDialog, rexEnableDialogButton: addCapabilityDialog.isAddButtonEnabled"
	     title="Add capability"
	     style="display: none;" class="rex-dialog">

		<form data-bind="submit: addCapabilityDialog.onConfirm.bind(addCapabilityDialog)">
			<label for="rex-new-capability-name">
				Capability name:
			</label>
			<input type="text" data-bind="textInput: addCapabilityDialog.capabilityName" id="rex-new-capability-name"
			       maxlength="150">

			<p id="rex-add-capability-validation-message">
			<span class="dashicons dashicons-dismiss"
			      data-bind="visible: (addCapabilityDialog.validationState() === 'error')"></span>
				<span class="dashicons dashicons-info"
				      data-bind="visible: (addCapabilityDialog.validationState() === 'notice')"></span>
				<span data-bind="html: addCapabilityDialog.validationMessage"></span>
			</p>
		</form>
	</div>

	<div id="rex-add-role-dialog"
	     data-bind="rexDialog: addRoleDialog, rexEnableDialogButton: addRoleDialog.isAddButtonEnabled"
	     title="Add role"
	     style="display: none;" class="rex-dialog">

		<!-- ko if: addRoleDialog.isRendered -->
		<form data-bind="submit: addRoleDialog.onConfirm.bind(addRoleDialog)">
			<p class="rex-dialog-section">
				<label for="rex-new-role-display-name">
					Display name:
				</label>
				<input type="text" data-bind="textInput: addRoleDialog.roleDisplayName" id="rex-new-role-display-name"
				       maxlength="150" placeholder="New Role Name">
			</p>

			<p class="rex-dialog-section">
				<label for="rex-new-role-name">
					Role name (ID):
				</label>
				<input type="text" data-bind="textInput: addRoleDialog.roleName" id="rex-new-role-name"
				       maxlength="150" placeholder="new_role_name">
			</p>

			<p class="rex-dialog-section">
				<label for="rex-new-role-copy-caps">
					Copy capabilities from:
				</label>
				<select id="rex-new-role-copy-caps" data-bind="value: addRoleDialog.roleToCopyFrom">
					<option data-bind="value: null">None</option>

					<!-- ko if: $root.defaultRoles().length > 0 -->
					<optgroup label="Built-In" data-bind="foreach: $root.defaultRoles">
						<option data-bind="text: displayName, value: $data"></option>
					</optgroup>
					<!-- /ko -->

					<!-- ko if: $root.customRoles().length > 0 -->
					<optgroup label="Custom" data-bind="foreach: $root.customRoles">
						<option data-bind="text: displayName, value: $data"></option>
					</optgroup>
					<!-- /ko -->
				</select>
			</p>

			<!--
			As an alternative to clicking the "Add Role" button, the user can
			confirm their inputs by pressing Enter.
			-->
			<input type="submit" name="hidden-submit-trigger" style="display: none;">
		</form>
		<!-- /ko -->
	</div>

	<div id="rex-delete-role-dialog"
	     data-bind="rexDialog: deleteRoleDialog, rexEnableDialogButton: deleteRoleDialog.isDeleteButtonEnabled"
	     title="Delete role"
	     style="display: none;" class="rex-dialog">

		<!-- ko if: deleteRoleDialog.isRendered -->
		<span>Select roles to delete:</span>

		<div class="rex-deletable-role-list-container">
			<table class="widefat rex-deletable-role-list">
				<tbody>
				<!-- ko if: $root.roles().length > 0 -->
				<!-- ko template: {
					name: 'rex-deletable-role-template',
					foreach: $root.roles
				} -->
				<!-- /ko -->
				<!-- /ko -->
				</tbody>
			</table>
		</div>
		<!-- /ko -->

		<!-- ko if: !deleteRoleDialog.isRendered() -->
		<div style="height: 400px">(Placeholder.)</div>
		<!-- /ko -->
	</div>

	<div id="rex-rename-role-dialog"
	     data-bind="rexDialog: renameRoleDialog, rexEnableDialogButton: renameRoleDialog.isConfirmButtonEnabled"
	     title="Rename role"
	     style="display: none;" class="rex-dialog">

		<!-- ko if: renameRoleDialog.isRendered -->
		<form data-bind="submit: renameRoleDialog.onConfirm.bind(renameRoleDialog)">
			<p class="rex-dialog-section">
				<label for="rex-role-to-rename">
					Select role to rename:
				</label>
				<select id="rex-role-to-rename" data-bind="value: renameRoleDialog.selectedRole">
					<!-- ko if: $root.defaultRoles().length > 0 -->
					<optgroup label="Built-In" data-bind="foreach: $root.defaultRoles">
						<option data-bind="text: (displayName() + ' (' + name() + ')'), value: $data"></option>
					</optgroup>
					<!-- /ko -->

					<!-- ko if: $root.customRoles().length > 0 -->
					<optgroup label="Custom" data-bind="foreach: $root.customRoles">
						<option data-bind="text: (displayName() + ' (' + name() + ')'), value: $data"></option>
					</optgroup>
					<!-- /ko -->
				</select>
			</p>

			<p class="rex-dialog-section">
				<label for="rex-new-role-display-name">
					New display name:
				</label>
				<input type="text" data-bind="textInput: renameRoleDialog.newDisplayName" id="rex-new-role-display-name"
				       maxlength="150" placeholder="New Role Name">
			</p>

			<input type="submit" name="hidden-submit-trigger" style="display: none;">
		</form>
		<!-- /ko -->
	</div>
</div>

<script type="text/html" id="rex-nav-item-template">
	<li class="rex-nav-item" data-bind="css: navCssClasses, click: $root.selectedCategory, visible: isNavVisible">
		<span class="rex-nav-toggle" data-bind="
			visible: (parent !== null),
			click: toggleSubcategories.bind($data),
			clickBubble: false">
		</span>
		<span data-bind="text: name, attr: { title: subtitle }" class="rex-nav-item-header"></span>

		<!-- ko if: isCapCountVisible -->
		<span class="rex-capability-count"
		      data-bind="css: {'rex-all-capabilities-enabled': areAllPermissionsEnabled},
		      attr: {title: enabledCapabilityCount() + ' of ' + totalCapabilityCount() + ' capabilities' }"><!--
		    ko if: isEnabledCapCountVisible
			--><span data-bind="text: enabledCapabilityCount" class="rex-enabled-capability-count"></span><!-- /ko
			--><!--
			ko if: $root.showTotalCapCountEnabled()
			--><span data-bind="text: totalCapabilityCount" class="rex-total-capability-count"></span><!-- /ko
		--></span>
		<!-- /ko -->
	</li>

	<!-- ko if: (subcategories.length > 0) -->
	<!-- ko template: {
			name: 'rex-nav-item-template',
			foreach: navSubcategories
		} -->
	<!-- /ko -->
	<!-- /ko -->
</script>

<script type="text/html" id="rex-category-template">
	<div class="rex-category" data-bind="css: cssClasses(), visible: isVisible, attr: { 'id': htmlId }">
		<div class="rex-category-header">
			<div class="rex-parent-category-name" data-bind="text: '[Parent category]'"></div>
			<div class="rex-category-name" data-bind="text: name"></div>
			<span data-bind="visible: (subtitle !== null), text: subtitle" class="rex-category-subtitle"></span>
		</div>
		<div class="rex-category-contents" data-bind="template: { name: contentTemplate }">
		</div>
	</div>
</script>

<script type="text/html" id="rex-default-category-content-template">
	<!-- ko if: subcategories.length > 0 -->
	<!-- ko template: {
		name: 'rex-category-template',
		foreach: sortedSubcategories
	 } -->
	<!-- /ko -->
	<!-- /ko -->

	<!-- ko if: (permissions().length > 0) -->
	<div class="rex-permission-list" data-bind="template: {name: 'rex-permission-template', foreach: permissions}">
	</div>
	<!-- /ko -->
</script>

<script type="text/html" id="rex-permission-table-template">
	<table class="widefat rex-permission-table">
		<thead>
		<tr>
			<th class="rex-category-name-column"></th>
			<!-- ko foreach: tableColumns -->
			<th scope="col" data-bind="text: title"></th>
			<!-- /ko -->
		</tr>
		</thead>

		<tbody data-bind="foreach: {data: sortedSubcategories, as: 'category'}">
		<tr data-bind="visible: isVisible">
			<th scope="row" data-bind="attr: {title: subtitle}">
				<label>
					<input type="checkbox" data-bind="checked: areAllPermissionsEnabled">
					<span data-bind="text: name"></span>
				</label>

				<div data-bind="visible: (subtitle !== null)">
					<input type="checkbox" readonly disabled style="visibility: hidden" title="Hidden placeholder">
					<span class="rex-category-subtitle" data-bind="text: subtitle"></span>
				</div>
			</th>

			<!-- ko foreach: {data: $parent.tableColumns, as: 'column'} -->
			<td data-bind="visible: !category.isBaseCapNoticeVisible()">
				<div data-bind="foreach: column.actions" class="">
					<!-- ko if: category.actions.hasOwnProperty($data) -->
					<!-- ko with: category.actions[$data] -->
					<!-- ko template: 'rex-permission-template' -->
					<!-- /ko -->
					<!-- /ko -->
					<!-- /ko -->
				</div>
			</td>
			<!-- /ko -->

			<!-- ko if: isBaseCapNoticeVisible -->
			<td class="rex-base-cap-notice" data-bind="attr: {colspan: $parent.tableColumns().length}">
				Uses "<span data-bind="text: getBaseCategory().name"></span>" capabilities.
			</td>
			<!-- /ko -->
		</tr>
		</tbody>
	</table>
</script>

<script type="text/html" id="rex-permission-template">
	<div class="rex-permission" data-bind="
					visible: isVisible,
					css: {
							'rex-is-redundant': isRedundant,
							'rex-is-deprecated-capability': capability.isDeprecated,
							'rex-is-explicitly-denied': capability.isExplicitlyDenied
						}">
		<label data-bind="attr: {title: capability.name}">
			<input
				data-bind="checked: capability.isEnabledForSelectedActor, enable: capability.isEditable"
				type="checkbox">
			<span data-bind="html: labelHtml" class="rex-capability-name"></span>
		</label>
		<span class="rex-permission-tip-trigger">[?]</span>
	</div>
</script>

<script type="text/html" id="rex-hierarchy-view-template">
	<!-- ko template: {
		name: 'rex-category-template',
		foreach: rootCategory.sortedSubcategories
	} -->
	<!-- /ko -->
</script>

<script type="text/html" id="rex-list-view-template">
	<div class="rex-permission-list" id="rex-permission-list-view"
	     data-bind="template: {name: 'rex-permission-template', foreach: allCapabilitiesAsPermissions}">
	</div>
</script>

<script type="text/html" id="rex-single-category-view-template">
	<!-- ko template: {
		name: 'rex-category-template',
		foreach: leafCategories
	} -->
	<!-- /ko -->
</script>

<script type="text/html" id="rex-deletable-role-template">
	<tr>
		<td class="rex-role-name-column" data-bind="attr: { title: name }">
			<label>
				<input
					data-bind="enable: $root.canDeleteRole($data),
						checked: $root.deleteRoleDialog.getSelectionState(name())"
					type="checkbox">
				<span data-bind="text: displayName"></span>
			</label>
		</td>
		<td class="rex-role-usage-column">
			<span data-bind="if: $root.isDefaultRoleForNewUsers($data)"
			      title="This is the default role for new users">
				Default role
			</span>
			<span data-bind="if: hasUsers && !$root.isDefaultRoleForNewUsers($data)"
			      title="This role is still assigned to one or more users">
				In use
			</span>
		</td>
	</tr>
</script>