/// <reference path="role-editor.ts" />

interface RexDialog {
	isOpen: KnockoutObservable<boolean>;

	options?: AmeDictionary<any>;
	autoCancelButton?: boolean;
	jQueryWidget: JQuery;

	onOpen?(event, ui);
	onClose?(event, ui);
}

/*
 * jQuery Dialog binding for Knockout.
 *
 * The main parameter of the binding is an instance of RexDialog. In addition to the standard
 * options provided by jQuery UI, the binding supports two additional properties:
 *
 *  isOpen - Required. A boolean observable that controls whether the dialog is open or closed.
 *  autoCancelButton - Set to true to add a WordPress-style "Cancel" button that automatically closes the dialog.
 *
 * Usage example:
 * <div id="my-dialog" data-bind="rexDialog: {isOpen: anObservable, autoCancelButton: true, options: {minWidth: 400}}">...</div>
 */
ko.bindingHandlers.rexDialog = {
	init: function (element, valueAccessor) {
		const dialog = ko.utils.unwrapObservable(valueAccessor()) as RexDialog;
		const _ = wsAmeLodash;

		let options = dialog.options ? dialog.options : {};
		if (!dialog.hasOwnProperty('isOpen')) {
			dialog.isOpen = ko.observable(false);
		}

		options = _.defaults(options, {
			autoCancelButton: _.get(dialog, 'autoCancelButton', true),
			autoOpen: dialog.isOpen(),
			modal: true,
			closeText: ' '
		});

		//Update isOpen when the dialog is opened or closed.
		options.open = function (event, ui) {
			dialog.isOpen(true);
			if (dialog.onOpen) {
				dialog.onOpen(event, ui);
			}
		};
		options.close = function (event, ui) {
			dialog.isOpen(false);
			if (dialog.onClose) {
				dialog.onClose(event, ui);
			}
		};

		let buttons = (typeof options['buttons'] !== 'undefined') ? ko.utils.unwrapObservable(options.buttons) : [];
		if (options.autoCancelButton) {
			//In WordPress, the "Cancel" option is usually on the left side of the form/dialog/pop-up.
			buttons.unshift({
				text: 'Cancel',
				'class': 'button button-secondary rex-dialog-cancel-button',
				click: function () {
					jQuery(this).closest('.ui-dialog-content').dialog('close');
				}
			});
		}
		options.buttons = buttons;

		//Do in a setTimeout so that applyBindings doesn't bind twice from element being copied and moved to bottom.
		window.setTimeout(function () {
			jQuery(element).dialog(options);
			dialog.jQueryWidget = jQuery(element).dialog('widget');

			if (ko.utils.unwrapObservable(dialog.isOpen)) {
				jQuery(element).dialog('open');
			}
		}, 0);


		ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
			jQuery(element).dialog('destroy');
		});
	},

	update: function (element, valueAccessor) {
		const dialog = ko.utils.unwrapObservable(valueAccessor()) as RexDialog;
		const $element = jQuery(element);
		const shouldBeOpen = ko.utils.unwrapObservable(dialog.isOpen);

		//Do nothing if the dialog hasn't been initialized yet.
		const $widget = $element.dialog('instance');
		if (!$widget) {
			return;
		}

		if (shouldBeOpen !== $element.dialog('isOpen')) {
			$element.dialog(shouldBeOpen ? 'open' : 'close');
		}
	}
};

ko.bindingHandlers.rexOpenDialog = {
	init: function (element, valueAccessor) {
		const clickHandler = function (event) {
			const dialogSelector = ko.utils.unwrapObservable(valueAccessor());

			//Do nothing if the dialog hasn't been initialized yet.
			const $widget = jQuery(dialogSelector);
			if (!$widget.dialog('instance')) {
				return;
			}

			$widget.dialog('open');
			event.stopPropagation();
		};
		jQuery(element).on('click', clickHandler);

		ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
			jQuery(element).off('click', clickHandler);
		});
	}
};

/*
 * The rexEnableDialogButton binding enables the specified jQuery UI button only when the "enabled" parameter is true.
 *
 * It's tricky to bind directly to dialog buttons because they're created dynamically and jQuery UI places them
 * outside dialog content. This utility binding takes a jQuery selector, letting you bind to a button indirectly.
 * You can apply it to any element inside a dialog, or the dialog itself.
 *
 * Usage:
 * <div data-bind="rexDialogButtonEnabled: { selector: '.my-button', enabled: anObservable }">...</div>
 * <div data-bind="rexDialogButtonEnabled: justAnObservable">...</div>
 *
 * If you omit the selector, the binding will enable/disable the first button that has the "button-primary" CSS class.
 */
ko.bindingHandlers.rexEnableDialogButton = {
	init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
		//This binding could be applied before the dialog is initialised. In this case, the button won't exist yet.
		//Wait for the dialog to be created and then update the button.
		const dialogNode = jQuery(element).closest('.ui-dialog');
		if (dialogNode.length < 1) {
			const body = jQuery(element).closest('body');

			function setInitialButtonState() {
				//Is this our dialog?
				let dialogNode = jQuery(element).closest('.ui-dialog');
				if (dialogNode.length < 1) {
					return; //Nope.
				}

				//Yes. Remove the event handler and update the binding.
				body.off('dialogcreate', setInitialButtonState);
				ko.bindingHandlers.rexEnableDialogButton.update(element, valueAccessor, allBindings, viewModel, bindingContext);
			}

			body.on('dialogcreate', setInitialButtonState);
			//If our dialog never gets created, we still want to clean up the event handler eventually.
			ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
				body.off('dialogcreate', setInitialButtonState);
			});
		}
	},

	update: function (element, valueAccessor) {
		const _ = wsAmeLodash;
		let options = ko.unwrap(valueAccessor());
		if (!_.isPlainObject(options)) {
			options = {enabled: options};
		}

		options = _.defaults(
			options,
			{
				selector: '.button-primary:first',
				enabled: false
			}
		);

		jQuery(element)
			.closest('.ui-dialog')
			.find('.ui-dialog-buttonset')
			.find(options.selector)
			.button('option', 'disabled', !ko.utils.unwrapObservable(options.enabled));
	}
};