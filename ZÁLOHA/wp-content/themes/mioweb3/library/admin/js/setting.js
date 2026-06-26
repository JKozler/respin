jQuery(function ($) {

	$('.mw_setting_body').mwSetting();
	$('.mw_setting_page_connections').mwConnectionsSetting();

});

;(function (factory) {

	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof exports !== 'undefined') {
		module.exports = factory(require('jquery'));
	} else {
		factory(jQuery);
	}

})(function ($) {

	var MwSetting = (function (element) {

		function _MwSetting(element) {

			this.$el = $(element);
			var obj = this;

			// alert before leaving page
			if(!this.$el.hasClass('mw_setting_dont_alert_on_leave'))
			{
				window.onbeforeunload = function () {
					if (obj.isEdited() && !obj.$el.hasClass('mw_setting_dont_alert_on_leave')) {
						return texts.before_leave_page;
					}
				}
			}

			// form on edit actions
			this.$el.find('.mw_onedit_action').mwOnEditAction({
				change: function (field_setting) {
					obj.setEdited();
				},
				add_new_setting: function (selector) {
					obj.$el.find(selector + ' .mw_onedit_action').mwOnEditAction({
						change: function (field_setting) {
							obj.setEdited();
						},
					});
				},
			});

			// object list
			this.$el.find(".mw_setting_list_container").mwSettingList({});

			// save setting
			this.$el.find('.mw_setting_save_but').click(function(e) {

				var formdata = obj.getForm();

				obj.addLoading();

				$.post(ajaxurl, 'action=mwSaveSetting&' + formdata, function (data) {

					obj.showMessage(data.html);

					if (data.success) {
						obj.setEdited(0);
					}
					else
					{

					}

					obj.removeLoading();

				}).fail(function (data) {

				});

				e.preventDefault();
			});

			// send setting
			this.$el.find('.mw_setting_send_but').click(function(e) {

				obj.$el.find('form').submit();
				e.preventDefault();

			});

			// save object
			this.$el.find('.mw_setting_save_object_but').click(function(e) {

				var formdata = obj.getForm();

				obj.addLoading();

				$.post(ajaxurl, 'action=mwSaveObject&' + formdata, function (data) {

					obj.showMessage(data.html);

					if (data.success) {
						obj.setEdited(0);
					}
					else
					{
						console.log('chyba');
					}

					obj.removeLoading();

				}).fail(function (data) {
					console.log(data);
					console.log('fail');
				});

				e.preventDefault();
			});

			// add object
			this.$el.find('.mw_setting_add_but').click(function(e) {

				var formdata = obj.getForm();

				obj.addLoading();

				$.post(ajaxurl, 'action=mwAddNewObject&' + formdata, function (data) {

					if (data.redirect) {
						obj.setEdited(0);
						window.location = data.redirect;
					} else {

						if (data.success) {

						}
						else
						{
							obj.showMessage(data.html);
						}

						obj.removeLoading();
					}

				}).fail(function (data) {

				});

				e.preventDefault();
			});

			// detail actions
			this.$el.find('.mw_setting_detail_delete_item').click(function () {
				var el = $(this);
				$(this).mwConfirm({
					show: true,
					text: texts.delete_item_confirm,
					onconfirm: function (confirm) {
						var item_id = confirm.$el.attr('data-id');
						var object_id = confirm.$el.attr('data-objectid');

						obj.addLoading();

						$.post(ajaxurl, {
							"action": "mwDeleteObjectItem",
							"item_id": item_id,
							"object_id": object_id,
						}, function (data) {
							window.location = data.admin_url;
						});
						return false;
					}
				});
			});

			// archive insert / update  datum
			this.$el.find('.mw_setting_detail_archive_item').click(function () {
				var el = $(this);
				var item_id = el.attr('data-id');
				var object_id = el.attr('data-objectid');
				obj.addLoading();
						$.post(ajaxurl, {
							'action': 'mwArchiveItem',
							'item_id': item_id,
							'object_id': object_id,
						}, function (data) {
							location.reload();
						});
						return false;
			});

			// delete archive meta
			this.$el.find('.mw_setting_detail_remove_archive_item').click(function () {
				var el = $(this);
				var item_id = el.attr('data-id');
				var object_id = el.attr('data-objectid');
				obj.addLoading();
				$.post(ajaxurl, {
							'action': 'mwDeArchiveItem',
							'item_id': item_id,
							'object_id': object_id,
						}, function (data) {
					       location.reload();
						});
						return false;
				});

			// status select
			this.$el.find(".mw_setting_sidebar_visibility").mwStatusSelect({
				onchange: function (val, status) {

					if(val == 'password_protected')
					{
						$('.mw_setting_password_protected_container').show();
						$('.mw_setting_password_protected_container input').focus();
					}
					else
					{
						$('.mw_setting_password_protected_container').hide();
						$('.mw_setting_password_protected_container input').val('');
					}

					obj.setEdited();
				}
			});

			this.$el.find('.mw_setting_publish_date_box').mwDatePicker({
				change: function () {
					var date = obj.$el.find('.mw_setting_publish_date_box .mw_datetime_field_date').val();
					var hour = obj.$el.find('.mw_setting_publish_date_box .mw_datetime_field_hour').val();
					var minute = obj.$el.find('.mw_setting_publish_date_box .mw_datetime_field_minute').val();
					obj.$el.find('.mw_setting_open_publish_date').html(date + ' ' + ('0'+hour).slice(-2) + ':' + ('0'+minute).slice(-2));
				}
			});

			this.$el.find('.mw_setting_open_publish_date').click(function () {
				obj.$el.find('.mw_setting_publish_date_container').show();
				document.addEventListener('click', outsideClickListener);
				return false;
			});

			const outsideClickListener = event => {
				var $target = $(event.target);
				if(!$target.closest('.mw_setting_publish_date_container').length) {
  			    	$('.mw_setting_publish_date_container').hide();
					removeClickListener();
				}
		    }

		    const removeClickListener = () => {
		        document.removeEventListener('click', outsideClickListener)
		    }

		}

		return _MwSetting;

	})();

	MwSetting.prototype.getForm = function() {
		return this.$el.find('form').serialize();
	}

	MwSetting.prototype.addLoading = function() {
		this.$el.find('.mw_setting_content').addClass('mw_loading');
	}

	MwSetting.prototype.removeLoading = function() {
		this.$el.find('.mw_setting_content').removeClass('mw_loading');
	}

	MwSetting.prototype.showMessage = function(message) {
		this.$el.find('.mw_setting_message_box').mwAddMessage({
			content: message,
			close: true,
			//autoHide: 10000,
		});
	}

	MwSetting.prototype.setEdited = function (set = '1') {
		$('body').attr('data-modified', set);
	}

	MwSetting.prototype.isEdited = function () {
		//console.log('edited');
		var modifi = $('body').attr('data-modified');
		if (modifi && modifi == '1')
			return true;
		else return false;
		//this.edited=true;
	}

	$.fn.mwSetting = function () {
		return this.each(function (index, el) {
			el.MwSetting = new MwSetting(el);
		});
	};

	// mw setting list filter
	var MwSettingFilter = (function (element, settings) {

		function _MwSettingFilter(element, settings) {

			this.$el = $(element);

			this.defaults = {
				onchange: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find('.mw_link_select').mwLinkSelect({
				onchange: function() {
					// on change
					if (obj.settings.onchange) {
						obj.settings.onchange.call();
					}
				},
			});

			this.$el.find('.mw_click_search').mwClickSearch({
				onchange: function() {
					// on change
					if (obj.settings.onchange) {
						obj.settings.onchange.call();
					}
				},
			});

		}

		return _MwSettingFilter;

	})();

	$.fn.mwSettingFilter = function (options) {
		return this.each(function (index, el) {
			el.MwSettingFilter = new MwSettingFilter(el, options);
		});
	};

	// mw setting list filter
	var MwSettingList = (function (element, settings) {

		function _MwSettingList(element, settings) {

			this.$el = $(element);

			this.defaults = {
				ondelete: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// item dropdown list
			this.$el.find(".mw_table_actions .mw_dropdown_button").mwDropdownButton({});

			// delete item
			this.$el.on("click", '.mw_table_list_item_delete', function () {
				var el = $(this);
				$(this).mwConfirm({
					show: true,
					text: texts.delete_item_confirm,
					onconfirm: function (confirm) {
						var item_id = confirm.$el.attr('data-id');
						var object_id = confirm.$el.attr('data-objectid');
						var container = confirm.$el.closest('tr');
						container.addClass('mw_loading');
						$.post(ajaxurl, {
							"action": "mwDeleteObjectItem",
							"item_id": item_id,
							"object_id": object_id,
						}, function (data) {
							container.slideUp('slow', function () {
								$(this).remove();
								if(obj.$el.find('.mw_table tbody tr').length==1)
								{
									if(obj.$el.find('.mw_pagination').length)
									{
										obj.reloadTable();
									}
									else
									{
										obj.$el.find('.mw_table').addClass('empty');
									}
								}
								if (obj.settings.ondelete) {
									obj.settings.ondelete.call(obj, item_id);
								}
							});
						}).fail(function (data) {
							console.log(data);
						});
						return false;
					}
				});
			});

			this.$el.on("click", '.mw_hierarchical_list_item_delete', function () {
				var el = $(this);
				$(this).mwConfirm({
					show: true,
					text: texts.delete_item_confirm,
					onconfirm: function (confirm) {
						var item_id = confirm.$el.attr('data-id');
						var object_id = confirm.$el.attr('data-objectid');
						var item = confirm.$el.closest('.mw_hierarchical_list_item');
						var container = confirm.$el.closest('.mw_hierarchical_list_item_container');
						item.addClass('mw_loading');
						$.post(ajaxurl, {
							"action": "mwDeleteObjectItem",
							"item_id": item_id,
							"object_id": object_id,
						}, function (data) {
							item.slideUp('slow', function () {
								var childs = container.find('ol');
								if(childs.length) {
									childs.find('.mw_hierarchical_list_item_container').addClass('childs_of_delete_branch');
									container.replaceWith( childs.html() );
									obj.$el.find(".childs_of_delete_branch .mw_dropdown_button").mwDropdownButton({}); // item dropdown list
									obj.$el.find('.childs_of_delete_branch').removeClass('childs_of_delete_branch');
								}
								else {
									container.remove();
								}
								if(obj.$el.find('.mw_hierarchical_list .mw_hierarchical_list_item_container').length==0)
								{
									obj.$el.find('.mw_hierarchical_list').addClass('empty');
								}
								if (obj.settings.ondelete) {
									obj.settings.ondelete.call(obj, item_id);
								}
							});
						});
						return false;
					}
				});
			});
			// create metakey for archive
			this.$el.on('click', ".mw_table_list_item_create_archive", function () {
				var item_id = $(this).attr('data-id');
				var object_id = $(this).attr('data-objectid');
				var container = $(this).closest('tr');
				container.addClass('mw_loading');
				$.post(ajaxurl, {
					'action': 'mwArchiveItem',
					'item_id': item_id,
					'object_id': object_id,
				}, function (data) {
					if(data)
					{
						container.slideUp('slow', function () {
							$(this).remove();
							if(obj.$el.find('.mw_table tbody tr').length===1)
							{
								obj.$el.find('.mw_table').addClass('empty');
							}
						});
					}
				});
				return false;

			});

			//  delete meta key archive
			this.$el.on('click', '.mw_table_list_item_renew', function () {

				var item_id = $(this).attr('data-id');
				var object_id = $(this).attr('data-objectid');
				var container = $(this).closest('tr');
				container.addClass('mw_loading');
				$.post(ajaxurl, {
					'action': 'mwDeArchiveItem',
					'item_id': item_id,
					'object_id': object_id,
				}, function (data) {

					if(data)
					{
						container.slideUp('slow', function () {
							$(this).remove();
							if(obj.$el.find('.mw_table tbody tr').length===1)
							{
								obj.$el.find('.mw_table').addClass('empty');
							}
						});
						location.reload();
					}
				});
				return false;
			});

			// restore item
			this.$el.on("click", '.mw_table_list_item_restore', function () {

				var item_id = $(this).attr('data-id');
				var object_id = $(this).attr('data-objectid');
				var container = $(this).closest('tr');
				container.addClass('mw_loading');
				$.post(ajaxurl, {
					"action": "mwRestoreObjectItem",
					"item_id": item_id,
					"object_id": object_id,
				}, function (data) {
					if(data)
					{
						container.slideUp('slow', function () {
							$(this).remove();
							if(obj.$el.find('.mw_table tbody tr').length==1)
							{
								obj.$el.find('.mw_table').addClass('empty');
							}
						});
					}
				});
				return false;

			});

			// change visibility
			this.$el.on("click", '.mw_checker_visibility .mw_checker_checked', function (e) {
				var container = $(this).closest('.mw_checker');
				var object_id = container.attr('data-objectid');
				var item_id = container.attr('data-id');

				container.addClass('loading');

				$.post(ajaxurl, {
					"action": 'mwSetObjectVisibility',
					"visibility": 'private',
					"item_id": item_id,
					"object_id": object_id,
				}, function (data) {
					container.removeClass('checked');
					container.removeClass('loading');
				});
			});
			this.$el.on("click", '.mw_checker_visibility .mw_checker_unchecked', function (e) {
				var container = $(this).closest('.mw_checker');
				var object_id = container.attr('data-objectid');
				var item_id = container.attr('data-id');

				container.addClass('loading');

				$.post(ajaxurl, {
					"action": 'mwSetObjectVisibility',
					"visibility": 'publish',
					"item_id": item_id,
					"object_id": object_id,
				}, function (data) {
					container.addClass('checked');
					container.removeClass('loading');
				});
				e.preventDefault();
			});

			// change comment status
			this.$el.on("click", '.mw_comment_change_status', function () {
				var el = $(this);

				var item_id = el.attr('data-comment');
				var status = el.attr('data-status');
				var remove = el.attr('data-remove');
				var container = el.closest('tr');
				container.addClass('mw_loading');
				$.post(ajaxurl, {
					"action": 'mwSetCommentStatus',
					"status": status,
					"item_id": item_id,
				}, function (data) {
					if(remove == '1')
					{
						container.slideUp('slow', function () {
							$(this).remove();
							if(obj.$el.find('.mw_table tbody tr').length==1)
							{
								obj.$el.find('.mw_table').addClass('empty');
							}
						});
					}
					else
					{
						if(status == '1')
						{
							container.removeClass('mw_comment_not_approved');
						}
						else if(status == '0')
						{
							container.addClass('mw_comment_not_approved');
						}
						container.removeClass('mw_loading');
					}
				});
				return false;

			});

			// reply comment
			this.$el.on("click", '.mw_comment_list_reply', function () {

				var el = $(this);
				var id = el.attr('data-comment');

				$(this).mwModal({
					but_text: texts.comment_save_reply,
					title: texts.comment_reply_title,
					onopen: function (modal) {

						var content = $('#mw_setting_comment_reply_container').html();
						modal.addContent(content);

					},
					onsave: function (modal) {

						var formdata = modal.getFormData();

						$.post(ajaxurl, 'action=mwSaveFastAddObject&return_type=table_row&comment_parent=' + id + '&object_id=comments&' + formdata, function (data) {
							if (data.success) {
								el.closest('tr').after(data.content);
								modal.close();
							}
							else
							{
								modal.removeLoading();
								modal.addMessage(data);
							}
						});

						return false;

					}

				});

				return false;
			});

			// bulk checkboxs
			this.$el.on("click", 'th.mw_table_bulk_col input', function (e) {
				if($(this).prop('checked'))
				{
					obj.$el.find( "td.mw_table_bulk_col input" ).prop( "checked", true );
				}
				else
				{
					obj.$el.find( "td.mw_table_bulk_col input" ).prop( "checked", false );
				}
				obj.checkBulk();
			});

			this.$el.on("click", 'td.mw_table_bulk_col input', function (e) {
				if(!$(this).prop('checked'))
				{
					obj.$el.find( "th.mw_table_bulk_col input" ).prop( "checked", false );
				}
				obj.checkBulk();
			});

			// filter
			this.$el.find('.mw_setting_filter').mwSettingFilter({
				onchange: function() {
					obj.reloadTable();
				},
			});



			// bulk actions
			this.$el.find('.mw_setting_bulk_container a').click(function(){
				var action = $(this).attr('data-action');

				if(action == 'delete') {
					$(this).mwConfirm({
						show: true,
						text: texts.delete_bulk_confirm,
						onconfirm: function (confirm) {
							obj.reloadTable('&bulk_action='+action);
							obj.hideBulk();
						},
					});
				} else if(action === 'renew'){
					obj.reloadTable('&bulk_action='+action);
					obj.hideBulk();
				//	location.reload()

				} else if(action === 'createArchive'){
					obj.reloadTable('&bulk_action='+action);
					obj.hideBulk();
				}
				else {
					obj.reloadTable('&bulk_action='+action);
					obj.hideBulk();
				}
				return false
			});

			// change page
			this.$el.on("change", '.mw_pagination select', function (e) {
				obj.reloadTable();
				//window.location = $(this).find('option:selected').attr('data-url');
			});

			this.$el.on("click", '.mw_pagination a', function (e) {
				obj.$el.find('.mw_pagination select').val($(this).attr('data-page'));
				obj.reloadTable();
				return false;
			});

			// empty trash
			this.$el.find('.mw_setting_empty_trash').click(function () {

				var el = $(this);
				$(this).mwConfirm({
					show: true,
					text: texts.empty_trash_confirm,
					but_text: texts.empty_trash,
					onconfirm: function (confirm) {

						var object_id = el.attr('data-objectid');
						var container = obj.$el.find('.mw_setting_list_table_container .mw_table');

						container.addClass('mw_loading');
						$.post(ajaxurl, {
							"action": 'mwEmptyTrash',
							"object_id": object_id,
						}, function (data) {
							container.find('tr:not(.mw_table_empty_info)').remove();
							container.removeClass('mw_loading').addClass('empty');
						});

						return false;
					}
				});

				return false;

			});

			// export
			this.$el.find('.mw_setting_open_export').click(function () {

				var object_id = $(this).data('objectid');

				$(this).mwModal({
					but_text: texts.export,
					onopen: function (modal) {

						$.post(ajaxurl, {
							"action": 'mwOpenObjectExportForm',
							"object_id": object_id,
						}, function (content) {
							modal.addContent(content);

							modal.$modal.find('[name="date_type"]').on('change', function() {
								var $datesContainer = modal.$modal.find('.mws_export_form__dates');
								if (!$datesContainer.length) {
									return;
								}

								if ($(this).val() !== '') {
									$datesContainer.slideDown();
								} else {
									$datesContainer.slideUp();
								}
							})
						});

					},
					onsave: function (modal) {

						var formdata = modal.getFormData();

						$.post(ajaxurl, 'action=mwCreateExportObjectItems&object_id=' + object_id + '&' + formdata, function (data) {
							if (data.success) {
								modal.addContent(data);
								modal.close();

								if (data.fileName && data.attachmentFileName) {
									var url = window.location.href;
									url += url.indexOf('?') > -1 ? '&mw_download_export=1' : '?mw_download_export=1'
									url += '&fileName=' + data.fileName;
									url += '&attachmentFileName=' + data.attachmentFileName;
									window.location.href = url;
								}
							}
							else
							{
								modal.removeLoading();
								modal.addMessage(data);
							}
						});

						return false;

					}

				});

				return false;
			});

			// set default item
			this.$el.on('click', '.mwtl_set_default', function () {
				var el = $(this);
				var container = obj.$el.find('.mw_setting_list_table_container .mw_table');

				$(this).mwConfirm({
					text: MioAdminjs.default_confirm,
					but_text: texts.continue,
					show: true,
					onconfirm: function (confirm) {

						container.addClass('mw_loading');
						var item_id = el.attr('data-itemid');

						obj.reloadTable('&set_default='+item_id);

					}
				});
				return false;
			});

			obj.createSort();

		}

		return _MwSettingList;

	})();

	MwSettingList.prototype.addTableLoading = function () {
		this.$el.find('.mw_table').addClass('mw_loading');
	}

	MwSettingList.prototype.getForm = function () {
		return this.$el.find('form').serialize();
	}

	MwSettingList.prototype.reloadTable = function (atrs = '') {
		this.addTableLoading();
		var formdata = this.getForm();

		var obj = this;
		//window.history.pushState("", "", '/newpage');
		$.post(ajaxurl, 'action=mwSetListReload&' + formdata + atrs, function (data) {
			obj.$el.find('.mw_setting_list_table_container').html(data);
			obj.$el.find(".mw_table_actions .mw_dropdown_button").mwDropdownButton({});
		});
	}

	MwSettingList.prototype.checkBulk = function () {
		var checked = false;
		if(this.$el.find('.mw_table_bulk_col input:checked').length > 0) {
			this.showBulk();
			this.$el.find('.mw_setting_bulk_count').html(this.$el.find('td.mw_table_bulk_col input:checked').length);
		} else {
			this.hideBulk();
		}
		if(this.$el.find('td.mw_table_bulk_col input:checked').length == this.$el.find('td.mw_table_bulk_col input').length)
		{
			this.$el.find( "th.mw_table_bulk_col input" ).prop( "checked", true );
		}
	}

	MwSettingList.prototype.showBulk = function () {
		this.$el.find('.mw_setting_filter').addClass('cms_nodisp');
		this.$el.find('.mw_setting_bulk_container').removeClass('cms_nodisp');
	}

	MwSettingList.prototype.hideBulk = function () {
		this.$el.find('.mw_setting_filter').removeClass('cms_nodisp');
		this.$el.find('.mw_setting_bulk_container').addClass('cms_nodisp');
	}

	MwSettingList.prototype.createSort = function () {

		var obj = this;

		this.$el.find('.mw_nestedsortable').nestedSortable({
			handle: '.mw_nestedsortable_item_wrap',
			items: '.mw_nestedsortable_item',
			toleranceElement: '> .mw_nestedsortable_item_wrap',
			placeholder: "sortable-highlight mw_hierarchical_list_item_container",
			start: function (event, ui) {
				ui.item.addClass('sorting');
			},
			stop: function (event, ui) {
				ui.item.removeClass('sorting');
			},
			update: function (evt, ui) {

				var changed_item = ui.item,
					new_parent_id;

				var item = changed_item.find('.mw_nestedsortable_item_wrap:first');
				item.addClass('mw_loading');

				if (changed_item.parent('.mw_nestedsortable').length > 0) {
					new_parent_id = 0;
				} else {
					new_parent_id = changed_item.parents('.mw_nestedsortable_item:first').find('.mw_nestedsortable_item_wrap').attr('data-id');
				}

				$.post(ajaxurl, {
					"action": "mwUpdateOrder",
					"item_id": item.attr('data-id'),
					"parent_id": new_parent_id,
					"order": changed_item.index(),
					"object_id": obj.$el.find('.mw_nestedsortable').attr('data-objectid'),
				}, function (data) {
					item.removeClass('mw_loading');
					console.log(data);
				});

			}
		});

	}

	$.fn.mwSettingList = function (options) {
		return this.each(function (index, el) {
			el.MwSettingList = new MwSettingList(el, options);
		});
	};

	// mw connections setting
	var MwConnectionsSetting = (function (element) {

		function _MwConnectionsSetting(element) {

			this.$el = $(element);

			var obj = this;

			this.$el.find('.mw_api_item').click(function () {

				var item = $(this);

				$(this).mwOpenApiConnectModal({
					onconnect: function (data) {
						item.removeClass('mw_api_item_disconnected').addClass('mw_api_item_connected');
					},
					ondisconnect: function (data) {
						item.removeClass('mw_api_item_connected').addClass('mw_api_item_disconnected');
					},
				});

				return false;
			});


		}

		return _MwConnectionsSetting;

	})();

	$.fn.mwConnectionsSetting = function () {
		return this.each(function (index, el) {
			el.MwConnectionsSetting = new MwConnectionsSetting(el);
		});
	};

});
