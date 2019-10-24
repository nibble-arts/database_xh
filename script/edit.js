


var database = {

	cmsroot: "",
	old_value: "",
	root: "plugins/database/",
	hrefs: {},


	/*******************************
	 * init(cmsroot)
	 *	set this.cmsroot
	 *	add edit button
	 *	add edit span tag
	 *	add double click event
	 *		(only executed if switch == true)
	 ******************************/
	init: function (cmsroot) {

		this.cmsroot = cmsroot;

		// init edit switch
		this.init_edit();

		// init fields
		this.init_fields();

	},


	init_edit: function () {

		obj = this;

		// add edit button if not exists
		if (!jQuery(".db_switch").length) {

			jQuery(".tplvoe_contentin")
				.append('<div class="db_switch"></div>');
		}


		// add event to database switch
		jQuery(".db_switch")

			.on("click", function() {

				// reset save status
				obj.reset_status();


				// in edit mode -> deactivate edit
				if (jQuery(this).attr("edit")) {
					obj.deactivate(this);
				}

				// activate edit
				else {
					obj.activate(this);
				}

			});

	},


	init_fields: function () {

		var obj = this;


		// get fields with field, id and table attributes
		var fields = jQuery("[field][id][table]");

		// iterate fields
		jQuery.each(fields, function () {

			// add double click event
			// add hover class
			jQuery(this)

				// remove doube click
				.off("dblclick")

				// suppress click
				.click(function(e) {
					e.stopPropagation();
				})

				// add doubleclick
				.dblclick( function (e) {

					// execute only if edit active
					if (jQuery(".db_switch").attr("edit"))
						database.set_edit(this);
				});


		});


		// add window click to save and reset edit
		jQuery(window)

			.keyup(function (e) {

				// end on enter or escape
				if (e.keyCode == 27) {
					database.save(false);
				}
			})

			.click(function () {
				database.save(true, this.cmsroot);
			});

	},



	/*******************************
	 * activate()
	 *	activate edit fields
	 *	remove a tags
	 *	show empty fields
	 ******************************/
	activate: function (obj) {

		// set edit attribute, doedit on obj and edit_hover for all
		jQuery(obj).attr("edit","edit");
		jQuery(obj).addClass("db_doedit");

		// set hover class
		jQuery("[field][id][table]").addClass("db_edit_hover");

		// deactivate hrefs
		var hrefs = jQuery("a span[field][id][table]");
		obj.hrefs = hrefs.parent();


		// replace hrefs with edit field
		jQuery.each(hrefs, function (idx, val) {

			// get content and add href id
			var content = jQuery(this).attr("editid", "href_"+idx);
			var parent = jQuery(this).parent();

			parent.replaceWith(content);
		});

	},


	/*******************************
	 * deactivate()
	 *	deactivate edit fields
	 *	restore a tags
	 ******************************/
	deactivate: function (obj) {

		// remove edit attribute and edit classes from switch
		jQuery(obj).removeAttr("edit");
		jQuery(obj).removeClass("db_doedit");

		// remove hover class
		jQuery("[field][id][table]").removeClass("db_edit_hover");

		// iterate saved hrefs
		jQuery.each(obj.hrefs, function (idx, val) {

			// get current content and add it to a tag
			var newContent = jQuery("[editid='href_"+idx+"']").clone();
			var newHref = obj.hrefs[idx];
			jQuery(newHref).append(newContent);

			// replace with new q tag - content
			jQuery("[editid='href_"+idx+"']")
				.replaceWith(newHref);

			jQuery("[editid='href_"+idx+"']")
				.removeAttr("editid");

		});
	},


	/*******************************
	 * set_edit(obj)
	 *	add span input to obj
	 ******************************/
	set_edit: function (obj) {

		var fieldName = jQuery(obj).attr("field");
		var fieldVal = jQuery(obj).text();

		this.old_value = fieldVal;


		// save data if dirty
		database.save(true);


		// suppress double click on object
		// add input field
		jQuery(obj)

			// initialize object
			.empty()
			.off("dblclick")

			// add input fields
			.append('<span class="db_edit"><span class="db_legend">'+  fieldName + '</span><input type="text" value="' + fieldVal + '" field="' + fieldName + '"></span>')

			.keyup(function (e) {

				// end on enter or escape
				if (e.keyCode == 13) {
					database.save(true);
				}
			});


		// hide legend on not empty fields
	//	if (fieldVal != "")
	//		jQuery(".db_legend").hide();


	},


	/*******************************
	 * reset_status()
	 *	reset all save stati
	 ******************************/
	reset_status: function () {

		// remove marks
		jQuery(".db_success,.db_fail")
			.removeClass("db_success")
			.removeClass("db_fail");

	},


	/*******************************
	 * save(exec)
	 *	save changed field to database
	 *	execute only if exec == true
	 ******************************/
	save: function (exec) {

		var value = "";
		var fieldName = "";
		var id = "";

		var obj = this;

		var edits = jQuery(".db_edit");

		jQuery.each(edits, function (idx, field) {

			var parent = jQuery(field.parentNode);
			value = jQuery(field).find("input")[0].value;

			// get attributes
			if (parent[0].attributes.field)
				fieldName = parent[0].attributes.field.value;
			if (parent[0].attributes.id)
				id = parent[0].attributes.id.value;
			if (parent[0].attributes.id)
				table = parent[0].attributes.table.value;

			if (exec && table && id && fieldName) {


				/************************************
				 * save to database if not escape
				 */

				var url = obj.cmsroot;

				url += "?admin=database_save";
				url += "&action=database_update";
				url += "&table=" + table;
				url += "&field=" + fieldName;
				url += "&value=" + value;
				url += "&id=" + id;
				url += "&normal";


				// send ajax
				jQuery.ajax({
					url: url
					})

					.success(function (data) {

						// reset stati
						obj.reset_status();


						// success from database
						if (data == "success") {

							jQuery("[id="+id+"][field="+fieldName+"]")
								.addClass("db_success");
						}

						// database error
						else {
							jQuery("[id="+id+"][field="+fieldName+"]")
								.addClass("db_fail");

						}

					})

					.error(function (xhr) {
						jQuery("[id="+id+"][field="+fieldName+"]")
							.addClass("db_fail")
							.text(obj.old_value);
					});

	//alert(url);		

	/************************************/

			}

			// reset field
			parent
				.text(value);

			field.remove();

			database.init_fields(obj.cmsroot);
		});
	}
}