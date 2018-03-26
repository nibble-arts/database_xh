


function database_init_edit(edit_attr) {

	var fields = jQuery("[field]");

	// iterate fields
	jQuery.each(fields, function (idx, field) {

		var fieldName = field.attributes.field.value;

		// add double click event
		// add hover class
		jQuery(field)

			// remove doube click
			.off("dblclick")

			// add hover class
			.addClass("db_edit_hover")

			// suppress click
			.click(function(e) {
				e.stopPropagation();
			})

			// add doubleclick
			.dblclick( {field: fieldName }, function (e) {
				database_set_edit(this);
			});

	});

	// add window click to save and reset edit
	jQuery(window)

		.keyup(function (e) {

			// end on enter or escape
			if (e.keyCode == 27) {
				database_save(false);
			}
		})

		.click(function () {
			database_save(true);
		});
}


function database_set_edit(obj) {

	var fieldName = obj.attributes.field.value;
	var fieldVal = jQuery(obj).text();

	// save data if dirty
	database_save(true);

	// suppress double click on object

	// add input field
	jQuery(obj)
		.empty()

		.off("dblclick")

		.removeClass("db_edit_hover")

		.append('<span class="db_edit"><span class="db_legend">'+  fieldName + '</span><input type="text" value="' + fieldVal + '" field="' + fieldName + '"></span>')

		.keyup(function (e) {

			// end on enter or escape
			if (e.keyCode == 13) {
				database_save(true);
			}
		});


	// hide legend on not empty fields
//	if (fieldVal != "")
//		jQuery(".db_legend").hide();

}


function database_save(exec) {

	var edits = jQuery(".db_edit");

	jQuery.each(edits, function (idx, field) {

		var parent = jQuery(field.parentNode);
		var value = jQuery(field).find("input")[0].value;
		var fieldName = parent[0].attributes.field.value;

		if (exec) {

/************************************
 * save to database if not escape
 */


alert("save "+value+" to field "+fieldName);

/************************************/

		}

		// reset field
		parent
			.text(value);

		field.remove();


		database_init_edit();
	});
}